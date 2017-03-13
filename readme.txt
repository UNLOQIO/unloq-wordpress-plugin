=== UNLOQ.io Passwordless authentication ===
Contributors: unloqer
Tags: two-factor, two factor, 2 step authentication, 2 factor, 2FA, admin, ios, android, authentication, encryption, iphone, log in, login, mfa, mobile, multi factor, unloq, password, passwordless, phone, secure, security, smartphone, ssl, strong authentication, tfa, two factor authentication, two step, wp-admin, wp-login, authorization
Requires at least: 3.5
Tested up to: 4.7.3
Stable tag: trunk
License: MIT
License URI: http://opensource.org/licenses/MIT

UNLOQ provides a free, easy to use and integrate, strong authentication systems that replaces passwords with your phone.

== Description ==

Looking to add Multi-factor authentication to your WordPress website?

## DESCRIPTION

UNLOQ helps you increase security of your Wordpress user accounts through a system of three password-less authentication mechanisms:
- Push notification (recommended)
- Time-based one-time password (optional, recommended as a back-up option)
- E-mail (optional, single factor)

Multi-factor authentication protects you from password re-use, phishing and keylogger attacks. No connection on your phone? We’ve got you covered. Click the menu button on the bottom right corner of the widget to see the other login option the application allows. Depending on your settings, these might come either as time based one time password (you’ll find the code under the Tokens menu option in the UNLOQ mobile app) or e-mail login (single factor, optional). In case of stolen phone you can deactivate your device at any moment, to protect data.

We believe it’s about your application & your users. Make the authentication system your own: personalise the appearance of the notification messages, customise your login widget and e-mail templates.

Want to take customisation to the next level? You can generate your custom branded multi-factor mobile application and upload it in the App Store / Google Play under your organisation name.

We've designed UNLOQ plugin so that anyone can install, configure and use it in a matter of minutes. For a step by step installation guide and answers to frequently asked questions, please visit us at https://docs.unloq.io/plugins/wordpress.

== Installation ==

### From your WordPress dashboard:

 1. Visit "Plugins > Add New"
 2. Search for "UNLOQ" and install the official plugin


### Manually via upload

 1. Download UNLOQ (https://github.com/UNLOQIO/wordpress-client/releases - latest release)
 2. Upload the "unloq.zip" into your plugins directory
 3. Install it

### Once activated
1. Login to UNLOQ at https://unloq.io/login
2. Create a WordPress Web Application with your site's domain
3. Configure the application (authentication mechanisms, design, etc.)
4. Go to the application's Settings > Widgets section and verify your domain
5. Go to the application's Settings > General > API Keys and add a new key. Make sure your app is in "Live" mode
6. Enter the API Key and Login Widget Key of your app bellow.
7. In your WordPress admin, choose which type of login you would want to allow (UNLOQ and/or regular passwords)

Note: the API Key is visible only once. You can get the login widget key by selecting the "Get Script" action from the Login Widget.

If you have any questions or installation issues, send us an e-mail at team@unloq.io . We will be happy to help you get started with UNLOQ.

== Screenshots ==
1. UNLOQ.io Login widget
2. UNLOQ.io Authentication request on your mobile device
3. UNLOQ.io Administrative interface

== Frequently Asked Questions ==

### Is UNLOQ really free?
The basic version is and will always be free. Your free account includes:
- unlimited applications, domains for up to 100 users per organisation
- e-mail and chat support
For more information about features and pricing, please visit us at https://unloq.io/pricing.

### How do you keep the lights on?
UNLOQ authentication system is offered under a freemium model. The basic plan is free and it will always be free, but we also offer premium plans that adds additional security features, detailed analytics and support features for your customers. You may want to consider them when implementing UNLOQ.

### Can existing users on my WordPress site sign in with UNLOQ after I install the plugin?
Of course they can. As long as your users register on their UNLOQ mobile apps using the same e-mail address as their WordPress accounts, they can start using UNLOQ without any other configurations.

### How do I add users?
Depending on your setting to allow or not self registration (see in Wordpress > Settings > General) you could:
a. Let user self register. On their first login, a new user will be created with the default role set up in Settings > General;
b. Register the users manually in Wordpress > Users. In this case you'll have to instruct your users to download the UNLOQ application and create a profile with the email you've used when you defined the user.

### How does UNLOQ accommodate logins for WordPress users who do not have smartphones or don’t have internet access on their phone?
UNLOQ offers three ways to authenticate: UNLOQ push notification, time-based one time password and e-mail login. Users without internet connection or without a smartphone may use one of the other two options. You can choose what authentication methods you want to make available to your users from UNLOQ administrative panel.

### What should I do if my phone is lost, stolen, or if I switch to a new phone?
If you lose or change your phone, you can deactivate your account from your device and reactivate it on a new phone. To deactivate your phone, go to https://unloq.io/deactivate.

### How secure is UNLOQ authentication system?
UNLOQ’s security architecture is fully distributed, which means UNLOQ stores no user passwords on its servers. We only store your e-mail, name and profile picture (the last two are not required, but might enhance the user experience), but these cannot be used to login into any service by themselves. Only you, from your phone (or e-mail in case of e-mail login) can authorize the authentication request. All data on your phone are kept encrypted with AES-256-CBC and we use SSL on all communication channels.


### Language
For now, UNLOQ is available in English. Please consider helping translate UNLOQ.



== Changelog ==
= 1.1.3 =
* Updated plugin description, screenshots, FAQs

= 1.1.2 =
* Updated the setup steps text
* Do not restrict UNLOQ init only on wp-login.php and wp-register.php in order to load on all sites.