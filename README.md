# gf-prosites
This repo contains two plugins: The User Registration Plugin and the Upgrade Plugin. They allow RocketGenius, Inc.'s Gravity Froms and WPMUdev's Pro Sites plugins work together.

#THE USER REGISTRATION PLUGIN
The first plugin is a custom version of Gravity form's User Registration form plugin. This allows you to bypass wpmudev's prosites signup page and register new sites with a custom form. 

## User Registration Installation
Extract the plugin from it's respective folder structure and upload it to your /plugins folder. Be sure to follow the notes inside the plugin before activating and using. 

## User Registration Plugin Notes

#### Prosites Trial
Enabling the Trial function in /wp-admin/network/admin.php?page=psts-settings will make the Pro Sites registration form not work as intended. Please make sure that the Trial is disabled. This can't be easily fixed within the plugin without rewriting classes within Pro Sites.

## User Registration FAQ
Q: I'm trying to add custom User Meta fields that will save to profile on registration. How do you handle custom user meta fields? 

A: You simply need to register the user meta once for Gravity Forms to recognize its existence. From there Gravity Forms can inject the fields into the user's meta. See https://www.gravityhelp.com/documentation/article/user-registration-add-on/#user-meta-fields for more.






#THE UPGRADE PLUGIN
The second plugin, the upgrade, allows you to bypass wpmudev's prosites auto-generated default upgrade/downgrade page (usually named "yoursite.com/prosite"). All functionality within Pro Sites remains, this little plugin just injects the Pro Site registration and the timestamp (UNIX) in the database.

The Pro Sites Upgrade form takes data from Pro Sites and manipulates it to form a very advanced form, which later, after all fields have been correctly filled in by the user and submitted, the plugin will inject a new expiration data into the prosites database and add used payment information for the upgrade form to later re-use and calculate.

## Upgrade Installation
Extract the plugin from it's respective folder structure and upload it to your /plugins folder. Be sure to follow the notes inside the plugin before activating and using. 

## Upgrade Plugin Notes

#### Extending Subscriptions

If the user has extended their subscription past 2 years they'll be booted to the home page without seeing a confirmation.

To avoid this bug (which is rare, why would anyone extend past 2 years? lol) set the Confirmation to a redirect or page (not text) in /wp-admin/admin.php?page=gf_edit_forms&view=settings&subview=confirmation&id=YOURFORMID



#### Discounts
An explanation about how the discounts are calculated, this only counts for upgrade/downgrade. The extending feature just gives the regular price:

Note: I used PayPal "subscription" as an example payment method here. However, this accounts for any payment method but the "keep paying for previous subscription" will be redundant as they have already paid the full price. Either way, they must pay the Full Price for the previous subscription, however they do so (be that in terms or direct).

The user gets a deducted price for their new subscription. Their previous subscription will be overwritten by the new one, they keep paying for the previous one and the new subscription gets a new price.

==== Example ====

I take a Basic subscription (5 usd/month for 12 months = 60 usd)
This means I’m going to pay 60 USD.

Now I want to upgrade after 6 months to Pro (19usd/month for 12 months = 228usd)
This means I get 6 months discount (calculated to the day!) (5/months for 6 months = 30 usd)

The final price I pay for the new subscription will be 228-30 = 198 USD
This will be split over 12 months, so this will mean that I will pay 16.5 USD/month (198/12)

What will happen is the following:
I keep paying 5 USD a month for another 6 months (30 USD)
I keep paying 16.5 USD for 12 months (198 USD)
The total price is 198+30 USD = 228 USD (original price for Pro)

==== Another example ===

I take a Pro subscription (19usd/month for 12 months = 228 usd)
I want to downgrade to Basic after 9 months (60 usd)

I get a discount of 3 months Pro (19usd/month for 3 months = 57 usd)
The final price for the Basic subscription will be 60-57 = 3 USD

This will be split over 12 months = 0.25 USD/month for 12 months.

This will mean I keep paying 3 months for Pro (19 times 3 = 57)
This will mean I keep paying 0.25 usd a month for 12 months Basic (3 usd)
Total price: 60 usd (original for Basic).

The site will automatically become free after their time expires. You will have to configure Pro Sites so that a free site will be deactivated if you wish to do so.



## Upgrade FAQ

Q: if I don't need the quarterly and yearly sections, can I just comment them out on the php file?

A: Yes! The quarterly and yearly sections listen to "term3months" and "term1year" respectively as a value from Gravity Forms. They won't get processed if those variables aren't assigned and thus not send to the PHP file, so leaving them out in Gravity Forms will do the trick as well 

Q:  i use stripe and "termmonth". What happen after exactly one month? i know stripe will process the payment but will the users site also be extended for another month?

A: The plugin in this post doesn't talk to the payment plugins, so the user's blog will be set to free after one month while the payment still continues. The best thing would be to set the term for the user to as high as the user is (going to) pay for. I know the PayPal plugin has a feature that talks to the User Registration Plugin from Gravity Forms, this way you can deactivate the blog if no payment has been processed automatically.
I'm not sure about Stripe.


