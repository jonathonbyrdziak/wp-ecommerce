=== WP e-Commerce ===
Contributors: Dan Milward, Tom Howard, Jeffry Ghazally
Donate link: http://getshopped.org/
Tags: e-commerce, shop, ecommerce, cart, dropshop, ajax, web2.0, paypal, authorize, exchange, stock control  
Requires at least: 2.7
Tested up to: 3.0.1
Stable tag: 3.7.6.9

WP e-Commerce is a Web 2.0 application designed with usability, aesthetics, and presentation in mind. 

== Description ==

The WP e-Commerce shopping cart plugin for WordPress is an elegant easy to use fully featured shopping cart application suitable for selling your products, services, and or fees online.

WP e-Commerce is a Web 2.0 application designed with usability, aesthetics, and presentation in mind. 

Perfect for:

* Bands & Record Labels
* Clothing Companies
* Crafters & Artists
* Books, DVDs & MP3 files 

== Installation ==

1. Upload the 'wp-e-commerce' folder to the '/wp-content/plugins/' directory
2. Activate the Plugin through the 'Plugins' menu in the WordPress Administration

= Updating =

Simply copy the new files across and replace the old files saving saving ones you've modified. If you have product images uploaded then do not overwrite the product_images folder or images folder. If you have downloadable products for sale do not overwrite the files folder.

When updating it is important that you do not overwrite the entire images folder. Instead you should copy over the contents of the new images folder into the existing images folder on your server - saving all the exiting product images you may have already uploaded.

If you experience database errors try de-activating and re-activating your plugin. 

== Changelog == 

= 3.7.6.8 =
* Fixed e-Commerce Tracking integration with Google Analytics for WordPress
* Use of site_url() within the Purchase Receipt/Admin Report for correct site URL
* Fixed presentation of Current Month widget on Store > Sales page
* Added stripslashes() to the output of custom Product meta on the front-end and within Sales > Products
* Added Show Product List RSS option within Store > Settings > Presentation to toggle visibility of the Product List RSS
* Fixed hourly reset of claimed stock
* Fixed Sales search, can now seek orders based on Checkout field details as well as Transaction ID
* Fixed implode issue on products tags
* More fixes to Checkout options
* Renamed "Last 30 Days" to "This Month" reflecting the behavior of the e-Commerce dashboard widget
* Fixed Delete purchases within Sales for single and bulk purchases
* Deleted categories are no longer visible within the tinyMCE Display Products panel
* Updated support for the latest release of DropShop
* Fix to force variation price to update on product page load
* Added new POT file for translations



= 3.7.6.7 =
* Added back the hourly Cron Job for clearing the Claimed Stock  
* Stripslahses added to transaction results message and Order Notes (removes the / in front of ' and ")
* Dashboard widget does not include sales that have been canceled by Paypal Pro
* Australia Post Shipping Module, fix for International quotes
* Fixed calculations on the Purchase Log and Shipping details pages
* Fixed Broken HTML tag in Packing Slip
* Permalinks Issue for WordPress 3.0 and the menu_nav_item custom post type conflict
* Memory Bump to increase PHP's memory limit if running on 32MB to avoid fatal error out of memory issues

= 3.7.6.6 =
* Edit Coupons Fixed and new Category Conditions added to the mix
* Checkout Options for drop-down boxes, radio buttons Fixed

= 3.7.6.5 =
* Fix Problem with Deprecated Theme files showing WPSC_TXT instead of their proper values.
* Fixed Integration with Gold Cart Plugin so Payment Gateways Show up on the Gateway Options page
* Added new nn_NO Translation Files
* Changed initial tax rate for UK to 17.5
* Fixed doubling Paypal Standard shipping bug
* Removed debug page from Paypal Standard 2.0
* Renamed Rogue Filters to align with Wp-e-Commerce Naming Conventions
* Fix admin product search so it works with multiple words.
* New Filters added to packing slip to allow better customization
* Changed working on Upgrades page to acknowledge Gold Cart Plugin difference
* :NOTE: For the theme file changes to take effect you will need to use the theme files from the wp-e-commerce directory, or replace the theme files in your uploads/wpsc/themes directory with the wp-e-commerce/themes folder
* Theme File Changes : Fixed Terms and Conditions over SSL
* Theme File Changes : Fixed Grid and List View to use hide_decimals options (so you can show/hide decimals on products-page)

= 3.7.6.4 =
* Show Product Shipping and Base Shipping Costs on Packing Slip
* Fix product and category permalinks problems that occurred in v3.7.6.3. Note: any 3.7.6.3 users that have edited products and/or categories, they will need to re-edit them after upgrading.
* Fixed an issue with the currency converter
* Renamed (removed the space in the filename) and updated the POT file for translators (wpsc-en_EN.pot)
* Removed unused install_and_update file from plugin folder
* Fixed some PHP warnings / notices

= 3.7.6.3 =
* Bugfix release
* Reset categoryAndShippingCountryConflist SESSION after each page load
* Stock not freed up when items are removed from the cart
* Permalinks broken when using nordic characters
* Tax included text strings aren't translated
* Total Amount is no longer negative is a discount used is larger than the total amount
* Norwegian translation
* Product list not showing in admin email report
* State shows Alabama (or other default region) if the country does not have states
* Shipping same as billing bug revisited
* Share this Fixes
* Added %find_us% tag so you can include how the customer found the website in your purchase report
* Fix pagination to work with search results.

= 3.7.6.2 =
* Paypal standard and Paypal standard 2.0 shipping send shipping details
* Google Checkout do not send shipping if shipping is 0
* New Spanish translations
* Removed 'Click to Download' from purchase receipts and transaction results page
* Removed old unused Google Base Code 
* New changes to purchase logs totals and tax
* Fixes to javascript errors from ratings.js
* Image Thumbnail Patch submitted by the amazing leewillis77
* Canonical URL / AISEOP fixes
* Backwards compatibility for update quantity code
* Encoded RSS URL so it validates
* New update message display below the auto-upgrade link in the WordPress plugin page submitted by the awesome husobj
* Image Cache fix should speed up those thumbnails

= 3.7.6.1 = 
* Bugfix release

= 3.7.6 = 
* Significant upgrade
* New Pagination Implementation
* Improved marketing, including Google Merchant Centre support
* gettext implementation for themes

= 3.7.5.4 =
* Implemented the new UPS Api for shipping calculation. The old API is being depricated
* Added switch to specify use of the UPS testing environment
* UPS API username, password and ID are now necessary for UPS rate quotes
* Added ability to limit the UPS services that are shown to customers

= 3.7.5.3 =
* Support for WordPress 2.9 canonical URLs for Products and Categories

= 3.7.5.2 =
* More Fixes to the Paypal Pro merchant file
* Image thumbnail size fixes
* Updated readme to mark plugin as working with 2.9
* Purchase log filtering bug fixed
* Fix for a bug when no shipping module is used where the shipping country and region were not being changed
* Remove button on checkout page now clears stock claims

= 3.7.5.1 =
* Fixes to the Paypal Pro merchant file
* Fixes to the Paypal Express Checkout merchant file
* Tracking email improvements
* HTML in descriptions does not break RSS (thanks to http://www.leewillis.co.uk)
* Category permalinks will now be regenerated properly on instalation
* Category list bug preventing viewing a product when viewing a category fixed.

= 3.7.5 =
* Added code for upgrades/additions from nielo.info and lsdev.biz,  we will be using this for new modules in the future.
* All In One SEO Pack compatibility bugfixes and improvements.
* CSV has had some work done on it, it now takes larger files, and associates a CSV file to a single category of your choice. We'd love to be able to allow users to add the categories and images as part of the CSV file. We will look into it more at a later date.
* SSL we fixed the image issue from beta1 and used James Collis recommended fix (using is_ssl() for our conditions) Thanks James!
* ‘Show list of Product Groups’ shows all Groups <- there may be some backwards compatibility issues (we havent encountered any but nevertheless if you spot any let us know)
* When duplicating products, their tags do not get duplicated for the new product. <- Oh yes they DO!
* Google Checkout now sends off Discount Coupons As well. And we fixed the `name` vs `code` Issue people mentioned in the forum
* Category shortcode backwards compatibility
* Fix Purchlogs - We had a lot of users that somehow by passed the 'fix purchase logs' page when upgrading from 3.6, so we added some better conditions to the mix and added it on to the debug page (a powerful wp-e-commerce page that is hidden from most users as it's usage is very corrosive backing up your DB and files is strongly recommended if not necessary when you work with this page).
* Valid XHTML for front end of wpec YAY!
* Fixed adding variations when adding products
* Sender from the 'resend email to buyer' link on the purchase log details page has been fixed
* Shipping Discount Bug that stopped shipping working at all.
* Categories Widget has had numerous changes –
* Better MU support. 
* Canadian Tax – Fixes
* US Tax –Fixes
* Product Categories Caching Issue  Resolved
* Coupons – ‘Apply to all Products’ and numerous bug fixes
* ‘Your Account’  done some fixes to it.
* ‘Accepted Payment’ goes straight to ‘Closed Order’
* Stock claims are now cleared when the cart is emptied
* Purchase log bulk actions now work
* PayPal gateway module fixes and improvements
* HTML Tables can now be added to product descriptions
* Flat Rate and Weight Rate improvements

= 3.7.4 =  
* Changes to shipping to fix the bugs from 3.7.3 with shipping and the new shipping_discount feature
* Fixes for variations under grid view

== Upgrade Notice ==

= 3.7.6.5 =
Fixes issue with deprecated WPSC_TXT constants being undefined and displaying in front end of site. All users encountering this issue are advised to upgrade. 
Also Fixed the gateway issue where DPS or Authorize do not show up on the Payments Option Page even after activating Gold Cart.

= 3.7.6.4 =
Fixes product/category permalink problems introduced in v3.7.6.3. All users that upgraded to 3.7.6.3 are encouraged to upgrade as soon as possible.

== Frequently Asked Questions ==

= Where do I find DropShop and Grid View =

From the BlogShop of course http://getshopped.org/extend/premium-upgrades/

= How do I customize WP e-Commerce =

First of all you should check out the presentation settings which are in the Shop Options page.

Advanced users can edit the CSS (and do just about anything). Not so advanced users can contact Instinct and either purchase professional support through GetShopped.org

http://getshopped.org

== Screenshots ==

Check out our flickr guides online

http://www.flickr.com/photos/danmilward/sets/72157594425079473/detail/
