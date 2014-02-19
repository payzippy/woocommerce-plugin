=== WooCommerce PayZippy Payment Gateway ===
Contributors: payzippy
Tags: WooCommerce, PayZippy, Payment Gateway, Debit Card, Credit Card, NetBanking
Requires at least: 3.0.1
Tested up to: 3.7.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Use PayZippy Payment Gateway in your WooCommerce store to accept Debit Cards, Credit Cards, Net Banking and EMI.

== Description ==

**WooCommerce PayZippy Payment Gateway**

This is the PayZippy Payment Gateway plugin for your WooCommerce store. Securely accept Debit Cards, Credit Cards, Net Banking and EMI modes of payment.

**Higher Conversions**

Benefit from our smart transaction routing and intuitive payment experience, that has driven higher success rates and lower cart abandonments for Flipkart.

**User Experience Redefined**

Let your customers enter their card details without leaving your website.
Offer the fastest checkout experience by enabling your customers to use their cards saved with PayZippy (coming soon).

**Great Merchant Support**

We bring Flipkart's customer obsessed service and learnings from solving payment issues. We are available 9 AM to 9 PM, all days of the week.

**100% Secure**

PayZippy has undergone stringent security audits (including PCI DSS) by industry experts.
PayZippy's real time risk engine analyses every transaction across 50+ risk parameters and notifies you about suspected fraudulent transactions by email/sms.

**Better Pricing**

Pay less as you grow.

== Installation ==

*Screenshots for the steps under Screenshots tab.*

1. Extract the plugin folder and copy the folder into your wp- content/plugins folder.

2. Log in to your WordPress admin account and click on the Plugins menu on the left.

3. You should see WooCommerce PayZippy Payment Gateway in the list of installed plugins. Click on the activate link to enable WordPress to use the PayZippy payment gateway.

4. To configure the plugin with your details, click on Woocommerce > Settings on the left menu. Click on Payment Gateways tab from the top menu.

5. Click on PayZippy. You can configure the plugin according to your requirements. 


**Brief description of the fields:**

- Enable/Disable : Enable or disable the PayZippy payment gateway for your Woocommerce store.

- Title : This will be displayed as payment method name on checkout.

- Description : Brief description for the payment gateway.

- Merchant ID : This will be given to you when you sign up for merchant account at PayZippy.

- Merchant Key ID : This will be given to you when you sign up for merchant account at PayZippy.

- Secret Key : This will be given to you when you sign up for merchant account at PayZippy.

- PayZippy API URL : The URL for PayZippy Charging API. Set it to https://www.payzippy.com/payment/api/charging/v1

- UI Integration Mode : The UI Integration Mode for displaying PayZippy payment form.
  In REDIRECT mode, the user will be redirected to PayZippy's website for payment and sent back to your site upon payment completion. 
  In IFRAME mode, the form to enter card details will be displayed in a HTML iframe element in your site.  
  For Net banking, the user will always be redirected to the bank's website.

- Hash Method : This is hash method using which your request will hashed.

- Payment Methods to Enable : Only the selected methods will be displayed during checkout.

- Allowed banks for Net Banking : Banks you want to allow for Net Banking.

- Allowed banks for 3 months EMI : Banks you want to allow for EMI of 3 months.

- Allowed banks for 6 months EMI : Banks you want to allow for EMI of 6 months.

- Allowed banks for 9 months EMI : Banks you want to allow for EMI of 9 months.

- Allowed banks for 12 months EMI : Banks you want to allow for EMI of 12 months.

- Return Page : This is the page user will be redirected to when the payment completes. Set it to Order Received, unless you are extending the plugin.
￼

== Frequently Asked Questions ==

= Which payment options are supported? =

With PayZippy, you can start accepting payments using Credit & Debit Cards (MasterCard, Visa, Maestro), Net Banking and EMI.

= Do you support International payments? =

PayZippy supports international payments, powered by advanced fraud detection mechanisms. 

= Is the payment experience customizable as per my website's look & feel? =

Yes, that's a must! We offer a unique iFrame option where your customers can enter their card details in a PayZippy iFrame within your website. Thus ensuring that your customers' payment experience is consistent with your own website's look & feel and the customers do not leave your website. You can also choose our Redirect option where your customers get redirected to a co-branded PayZippy page for entering the card details.

= How & when will I receive the money for the payments made on my website? = 

All funds will be settled to your preferred bank account within two working days of the date of transaction.

= How can I contact PayZippy? =

Feel free to reach us at contactus@payzippy.com. We'll be happy to answer your queries/concerns.

== Screenshots ==

1. Activating the WooCommerce PayZippy Payment Gateway
2. Navigating to the Payment Gateways tab to configure your pluging
3. Configuration for the plugin

== Changelog ==

= 1.0 =
* Public Release
