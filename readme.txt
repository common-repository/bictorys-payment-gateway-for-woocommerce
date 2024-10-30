=== Bictorys Payment Gateway for WooCommerce ===
Contributors: bictorys
Tags: bictorys, woocommerce, payment, payment gateway, bictorys plugins
Requires at least: 5.8
Tested up to: 6.5
Stable tag: 1.0.5
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Bictorys for WooCommerce allows your to accept secure payments from multiple local and global payment channels.

== Description ==

Bictorys makes it easy for businesses in Senegal, Ivory Cost, etc... to accept secure payments from multiple local and global payment channels. Integrate Bictorys with your store today, and let your customers pay you with their choice of methods.

With Bictorys for WooCommerce, you can accept payments via:

* Credit/Debit Cards — Visa, Mastercard, American Express
* Bank transfer
* Mobile money
* Masterpass
* EFT
* USSD
* Visa QR
* Many more coming soon

== External Services ==
This plugin relies on the following third-party services:

**Bictorys Payment Gateway**
    - **Purpose:** Used for processing payments securely.
    - **Service URL:** [Bictorys](https://bictorys.com)
    - **Data Sent:** Transaction details, including payment information and authorization data.

= Why Bictorys? =

* Start receiving payments instantly—go from sign-up to your first real transaction in as little as 15 minutes
* Simple, transparent pricing—no hidden charges or fees
* Modern, seamless payment experience via the Bictorys Checkout — [Try the demo!](https://bictorys.com/demo/checkout)
* Advanced fraud detection
* Understand your customers better through a simple and elegant dashboard
* Access to attentive, empathetic customer support 24/7
* Free updates as we launch new features and payment options
* Clearly documented APIs to build your custom payment experiences

= Note =

This plugin is meant to be used by merchants in Senegal, Ivory Cost, etc...


== Installation ==

*   Go to __WordPress Admin__ > __Plugins__ > __Add New__ from the left-hand menu
*   In the search box type __Bictorys Payment Gateway for WooCommerce__
*   Click on Install now when you see __Bictorys Payment Gateway for WooCommerce__ to install the plugin
*   After installation, __activate__ the plugin.


= Bictorys Setup and Configuration =
*   Go to __WooCommerce > Settings__ and click on the __Payments__ tab
*   You'll see Bictorys listed along with your other payment methods. Click __Set Up__
*   On the next screen, configure the plugin. There is a selection of options on the screen. Read what each one does below.

1. __Enable/Disable__ - Check this checkbox to Enable Bictorys on your store's checkout
2. __Title__ - This will represent Bictorys on your list of Payment options during checkout. It guides users to know which option to select to pay with Bictorys. __Title__ is set to "Debit/Credit Cards" by default, but you can change it to suit your needs.
3. __Description__ - This controls the message that appears under the payment fields on the checkout page. Use this space to give more details to customers about what Bictorys is and what payment methods they can use with it.
4. __Test Mode__ - Check this to enable test mode. When selected, the fields in step six will say "Test" instead of "Live." Test mode enables you to test payments before going live. The orders process with test payment methods, no money is involved so there is no risk. You can uncheck this when your store is ready to accept real payments.
5. __Payment Option__ - Select how Bictorys Checkout displays to your customers. A popup displays Bictorys Checkout on the same page, while Redirect will redirect your customer to make payment.
6. __API Keys__ - The next two text boxes are for your Bictorys API keys, which you can get from your Bictorys Dashboard. If you enabled Test Mode in step four, then you'll need to use your test API keys here. Otherwise, you can enter your live keys.
7. __Additional Settings__ - While not necessary for the plugin to function, there are some extra configuration options you have here. You can do things like add custom metadata to your transactions (the data will show up on your Bictorys dashboard) or use Bictorys's [Split Payment feature](https://bictorys.com/docs/payments/split-payments). The tooltips next to the options provide more information on what they do.
8. Click on __Save Changes__ to update the settings.

To account for poor network connections, which can sometimes affect order status updates after a transaction, we __strongly__ recommend that you set a Webhook URL on your Bictorys dashboard. This way, whenever a transaction is complete on your store, we'll send a notification to the Webhook URL, which will update the order and mark it as paid. You can set this up by using the URL in red at the top of the Settings page. Just copy the URL and save it as your webhook URL on your Bictorys dashboard under __Settings > API Keys & Webhooks__ tab.

If you do not find Bictorys on the Payment method options, please go through the settings again and ensure that:

*   You've checked the __"Enable/Disable"__ checkbox
*   You've entered your __API Keys__ in the appropriate field
*   You've clicked on __Save Changes__ during setup

== Frequently Asked Questions ==

= What Do I Need To Use The Plugin =

*   A Bictorys merchant account—use an existing account or [create an account here](https://bictorys.com)
*   An active [WooCommerce installation](https://docs.woocommerce.com/document/installing-uninstalling-woocommerce/)
*   A valid [SSL Certificate](https://docs.woocommerce.com/document/ssl-and-https/)

= WooCommerce Subscriptions Integration =

*	The [WooCommerce Subscriptions](https://woocommerce.com/products/woocommerce-subscriptions/) integration only works with WooCommerce v2.6 and above and WooCommerce Subscriptions v2.0 and above.

*	No subscription plans is created on Bictorys. The [WooCommerce Subscriptions](https://woocommerce.com/products/woocommerce-subscriptions/) handles all the subscription functionality.

*	If a customer pays for a subscription using a MasterCard or Visa card, their subscription will renew automatically throughout the duration of the subscription. If an automatic renewal fail their subscription will be put on-hold and they will have to login to their account to renew the subscription.

*	For customers paying with a Verve card, their subscription can't be renewed automatically, once a payment is due their subscription will be on-hold. The customer will have to login to his account to manually renew his subscription.

*	If a subscription has a free trial and no signup-fee, automatic renewal is not possible because the order total will be 0, after the free trial the subscription will be put on-hold. The customer will have to login to his account to renew his subscription. If a MasterCard or Visa card is used to renew subsequent renewals will be automatic throughout the duration of the subscription, if a Verve card is used automatic renewal isn't possible.


== Changelog ==

= 1.0.5 - April 16, 2024 =
*   New: Additional admin settings parameters
*   Tweak: CSS changes
*   Tweak: Error handling for API URI calling
*   Update: Payment Images

= 1.0.0 - Novembre 7, 2023 =
*   New: Add support for WooCommerce checkout block
*   Tweak: WooCommerce 8.1 compatibility
*   Tweak: Pass order currency when making payment using saved cards
*   Update: Load Bictorys InlineJS (Popup) V2 in the custom payment gateways

== Screenshots ==

1. Bictorys displayed as a payment method on the WooCommerce payment methods page

2. Bictorys WooCommerce payment gateway settings page

3. Bictorys on WooCommerce Checkout