# Email Layout Generator for Freeform Pro Composer Forms (QDS Magicator, Beta)

With Composer for Freeform Pro users can create forms on the fly, creating layouts
as needed. Unfortunately, any added fields that need to be in an email need to also
be added to the notification. This add-on will use the email notification hooks
to fetch the composer layout and merge the post data with that layout. This
ensures that emails will remain in sync with Composer layout changes. Just create
a notification, enable that notification in this extension's settings and add the
{composer_layout_output} variable to the created notification. That's it.

![Screen Shot](/screengrab.png?raw=true "QDS Magicator for EE3")

Hooks used:
- freeform_recipient_email
- freeform_module_admin_notification
- freeform_module_user_notification
