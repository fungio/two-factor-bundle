How to create an account and configure TOTP
========================================

Create an encryption key
---------------------

![Encryption key](../images/encryption_key.png)

Copy the encryption key and paste to ``config.yml``:

![Encryption key config](../images/encryption_key_config.png)

But we recommend that you keep this value in `parameters.yml` and `parameters.yml.dist` and add reference to variable in `config.yml`.

Create an account
--------------

![Create account - step 1](../images/create_account_step_1.png)

Choose one of the above options and provide your data:

![Create account - step 2](../images/create_account_step_2.png)

Add account in your mobile app
------------------------------

1. Log in to your website and go to **"/2fas/index"**.

This is a simple dashboard to show and manage your settings:

![Dashboard](../images/dashboard.png)

> **Note**
>
>This dashboard comes from our demo application and has included our css styles.
>After installing this bundle by default you haven't any styles so you get a white, plain html page.
>But if you want, you can include it.

2. Click on the *"Configure"* under the TOTP channel.

3. You will see the configuration page:

![Configure TOTP](../images/configure_totp.png)

4. Open your mobile app:

![Mobile app - 1](../images/mobile_app_1.png)

5. Scan QR code:

![Mobile app - 2](../images/mobile_app_2.png)

6. Your mobile app will generate a code:

![Mobile app - 3](../images/mobile_app_3.png)

7. Type this code in form and click **Submit**

8. Configuration is complete but the second factor is still disabled:

![Configure totp complete](../images/configure_totp_complete.png)

9. Go to console and enable the second factor:

![Fungio Enable](../images/fungio_enable.png)

10. Now you will be protected by 2FAS, next time you login, after your login form you will see the second factor form:

![Check code](../images/check_code.png)