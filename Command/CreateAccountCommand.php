<?php

namespace TwoFAS\TwoFactorBundle\Command;

use Psr\SimpleCache\CacheInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use TwoFAS\Encryption\Cryptographer;
use TwoFAS\TwoFactorBundle\Model\Entity\OptionInterface;
use TwoFAS\TwoFactorBundle\Model\Persister\ObjectPersisterInterface;
use TwoFAS\Account\Exception\Exception as AccountException;
use TwoFAS\Account\TwoFAS;

/**
 * Creates Two Factor Authentication Service Account.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\Command
 */
class CreateAccountCommand extends ContainerAwareCommand
{
    /**
     * @var TwoFAS
     */
    protected $sdk;

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('twofas:account:create')
            ->setDescription('Create new 2FAS Account');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->sdk = $this->getSdk();

        $this->welcome($output);
        $this->validateConfiguration();

        $accountExists = $this->isAccountExist($input, $output);
        $email         = $this->getEmail($input, $output);
        $password      = $this->getPassword($input, $output, $accountExists);

        if (!$accountExists) {
            $this->createAccount($email, $password);
        } else {
            $this->createIntegration($email, $password);
        }

        $this->getCache()->clear();

        $output->writeln('<info>Your Two FAS Account Created Successfully!</info>');
    }

    /**
     * @param OutputInterface $output
     */
    protected function welcome(OutputInterface $output)
    {
        $output->writeln([
            '',
            '============================================================',
            'Welcome to Two Factor Authentication Service Account Creator',
            '============================================================',
            '',
        ]);
    }

    /**
     * @throws \Exception
     */
    protected function validateConfiguration()
    {
        $optionPersister = $this->getOptionPersister();

        if (is_null($this->getEncryptionKey())) {
            throw new \Exception('Two FAS Encryption Key is not set! Run "twofas:encryption-key:create first."');
        }

        if (count($optionPersister->getRepository()->findAll()) > 0) {
            throw new \Exception('Previous configuration has detected! Run "twofas:account:delete first if you want create new account or use another credentials."');
        }
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return bool
     */
    protected function isAccountExist(InputInterface $input, OutputInterface $output)
    {
        $helper   = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Do you have an account in 2FAS?',
            ['no', 'yes']
        );
        $question->setErrorMessage('Answer %s is invalid.');

        $accountExists = $helper->ask($input, $output, $question);

        if ('yes' === $accountExists) {
            return true;
        }

        return false;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return string
     */
    protected function getEmail(InputInterface $input, OutputInterface $output)
    {
        $validator   = $this->getValidator();
        $helper      = $this->getHelper('question');
        $question    = new Question('Please enter your email: ');
        $constraints = $this->getEmailConstraints();

        $question->setValidator(function($value) use ($validator, $constraints) {

            $errors = $validator->validate($value, $constraints);

            if ($errors->count() > 0) {

                $messages = array_map(function(ConstraintViolation $error) {
                    return $error->getMessage();
                },
                    iterator_to_array($errors));

                throw new \Exception(implode("\n", $messages));
            }

            return $value;
        });

        return $helper->ask($input, $output, $question);
    }

    /**
     * @return array
     */
    protected function getEmailConstraints()
    {
        $notBlankConstraint          = new NotBlank();
        $notBlankConstraint->message = 'The email can not be empty';
        $emailConstraint             = new Email();

        return [$notBlankConstraint, $emailConstraint];
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param boolean         $accountExists
     *
     * @return string
     */
    protected function getPassword(InputInterface $input, OutputInterface $output, $accountExists)
    {
        $helper = $this->getHelper('question');

        if ($accountExists) {
            $question = new Question('Please enter your password: ');
            $question->setHidden(true);
            $question->setHiddenFallback(false);
            $question->setValidator(function($value) {
                if (trim($value) == '') {
                    throw new \Exception('The password can not be empty');
                }

                return $value;
            });

            return $helper->ask($input, $output, $question);
        }

        return bin2hex(openssl_random_pseudo_bytes(8));
    }

    /**
     * @param string $email
     * @param string $password
     *
     * @throws AccountException
     */
    protected function createAccount($email, $password)
    {
        $this->sdk->createClient($email, $password, $password, 'symfony');
        $this->createIntegration($email, $password);
    }

    /**
     * @param string $email
     * @param string $password
     *
     * @throws AccountException
     */
    protected function createIntegration($email, $password)
    {
        $cryptographer = $this->getCryptographer();

        $host = gethostname();
        $ip   = gethostbyname($host);
        $this->sdk->generateOAuthSetupToken($email, $password);

        $integration = $this->sdk->createIntegration('Symfony [' . $host . '@' . $ip . ']');
        $this->sdk->generateIntegrationSpecificToken($email, $password, $integration->getId());
        $key = $this->sdk->createKey($integration->getId(), 'symfony-key');

        $this->saveOption(OptionInterface::INTEGRATION, $cryptographer->encrypt($integration->getId()));
        $this->saveOption(OptionInterface::LOGIN, $cryptographer->encrypt($integration->getLogin()));
        $this->saveOption(OptionInterface::TOKEN, $cryptographer->encrypt($key->getToken()));
        $this->saveOption(OptionInterface::STATUS, 0);
    }

    /**
     * @param string $name
     * @param string $value
     */
    protected function saveOption($name, $value)
    {
        $optionPersister = $this->getOptionPersister();

        /** @var OptionInterface|null $option */
        $option = $optionPersister->getRepository()->findOneBy(['name' => $name]);

        if (is_null($option)) {
            $option = $optionPersister->getEntity();
            $option->setName($name);
        }

        $option->setValue($value);
        $optionPersister->saveEntity($option);
    }

    /**
     * @return TwoFAS
     */
    private function getSdk()
    {
        return $this->getContainer()->get('two_fas_two_factor.sdk.account');
    }

    /**
     * @return ObjectPersisterInterface
     */
    private function getOptionPersister()
    {
        return $this->getContainer()->get('two_fas_two_factor.option_persister');
    }

    /**
     * @return string
     */
    private function getEncryptionKey()
    {
        return $this->getContainer()->getParameter('two_fas_two_factor.encryption_key');
    }

    /**
     * @return Cryptographer
     */
    private function getCryptographer()
    {
        return $this->getContainer()->get('two_fas_two_factor.encryption.cryptographer');
    }

    /**
     * @return ValidatorInterface
     */
    private function getValidator()
    {
        return $this->getContainer()->get('validator');
    }

    /**
     * @return CacheInterface
     */
    protected function getCache()
    {
        return $this->getContainer()->get('two_fas_two_factor.cache.storage');
    }
}
