Hi there looker!

Well, here it is! The Gravity Forms extension for WPMUdev's Pro Sites.

Before we get started, this is what you'll need:
1. Gravity Forms (I used the latests beta (1.9.2?))
2. User Registration add-on
3. Cake & 12 cups of coffee
4. A few hours of your time (don't give up!)

What you'll need to do:
1. Import the .json file in to your main blog (wp-admin/admin.php?page=gf_export&view=import_form)
2. Look at the form thouroughly and edit what you need to edit. Read the instructions carefully and don't delete anything.
3. You can add any fields you'd like - you can even re-arrange them. But before you do that - let's test the PHP file first:
4. Open \prositesupgradecontents\wp-content\plugins\prositesupgrade\prositesupgrade.php in NotePad++.exe
5. Read through that file carefully. You probably only need to edit 4 lines excluding your prices for the form to work.
6. Update your prices carefully in the PHP file to your own prices
7. Now it's time to edit the form.
8. Edit the prices carefully, don't make mistakes D: - Do not edit any names or values yet. Keep the descriptions for now.

9. Finally, go to User Registration (wp-admin/admin.php?page=gf_user_registration)
10. hit that Add New button like no tomorrow
11. Select "Update User".
12. Select the new form.
13. Keep the entries empty and hit Update like there is a tomorrow. (yup)

14. Now it's time to upload the plugin files to /wp-content/plugins/ (note how I kept the hierarchy ^^)
15. Activate the plugin on your main blog (do not network activate it!). Does it activate? Yes? This means you've edited the PHP file correctly! :D If not, revisit the file and/or run it through http://www.piliapp.com/php-syntax-check/

16. Add the following shortcode on your desired upgrade page (default is yoursite.com/premium-site/): [upgrade-prosite-page]

17. Test out the form, make a few random sites with random users. Do not test this out on a super admin account.
18. After everything's working, go back to your form editor and take a good look at the conditional logics before you change any names - make backups of your progress and test them out every time. I know, it's a lot to process through - Get a noteblock :)

19. Finally, edit the CSS file to your liking, note that you might need to purge the file or clear your cache after editing.
20. Add the [redirect-to-gforms-upgrade] shortcode on yoursite.com/pro-sites/
21. Now your users get redirected to your New form! Enjoy :)

== Running into problems? ==
1. You wanna fight about it?
2. Keep a log of your actions and take note of what step you're in.
3. Visit http://premium.wpmudev.org/forums/topic/new-unofficial-plugin-gravity-forms-integration-with-wpmudev-pro-sites and post the problems you've run into, please keep them specifically to this plugin.
4. Too difficult? I can understand. Want me to install it for just 100$? http://premium.wpmudev.org/pro/sybre-waaijer/
5. There are many other pro's available out there which are more than willing to help you too: http://premium.wpmudev.org/pros/
6. Be patient, take a break. This took me 5 days to write (so many conditions! D:)

Enjoy!