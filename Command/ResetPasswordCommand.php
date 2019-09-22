<?php

namespace Fungio\TwoFactorBundle\Command;

use Exception;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Fungio\Account\Exception\Exception as AccountException;
use Fungio\Account\Exception\NotFoundException;
use Fungio\Account\Fungio;

/**
 * Send instructions to reset password to email.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Command
 */
class ResetPasswordCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('fungio:reset-password')
            ->setDescription('Reset your 2FAS password.');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $this->getEmail($input, $output);

        $this->resetPassword($email);

        $output->writeln('<info>Instructions on how to reset your password were sent to your email. Please check your inbox.</info>');
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

                throw new Exception(implode("\n", $messages));
            }

            return $value;
        });

        return $helper->ask($input, $output, $question);
    }

    /**
     * @param string $email
     *
     * @throws AccountException
     */
    protected function resetPassword($email)
    {
        try {
            $sdk = $this->getSdk();

            $sdk->resetPassword($email);

        } catch (NotFoundException $e) {
            throw new AccountException('E-mail does not exists.');
        }
    }

    /**
     * @return array
     */
    protected function getEmailConstraints()
    {
        $notBlankConstraint          = new NotBlank();
        $notBlankConstraint->message = 'The email can not be empty';
        $emailConstraint             = new Email();
        $emailConstraint->message    = 'Enter a valid email address';

        return [$notBlankConstraint, $emailConstraint];
    }

    /**
     * @return Fungio
     */
    private function getSdk()
    {
        return $this->getContainer()->get('two_fas_two_factor.sdk.account');
    }

    /**
     * @return ValidatorInterface
     */
    private function getValidator()
    {
        return $this->getContainer()->get('validator');
    }
}
