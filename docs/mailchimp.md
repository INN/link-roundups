## Using Link Roundups Mailchimp Integration

Using the Mailchimp API, Link Roundups can send Roundups as newsletters via a MailChimp account.

## Prerequisites: Setup a Template and List in MailChimp

You'll need to create a template and a list if you don't already have them in MailChimp.

## Getting an API key from MailChimp

In order to use the MailChimp features of Argo Links, you'll need to sign up for an API key. This is free and easy!

1. Log into MailChimp.
2. Click on your account in the upper-right corner. In the drop-down menu, click "Account".
3. Click on "Extras", then "API keys".
4. Click "Create A Key". A new key will appear in the list.
5. Click the "Label" field of your new API key. Name it after the site you're using Argo Links on. For example: "example.com argo links"
6. Copy the API key. This will be a long string of characters from 0-9 and a-f.
7. On your website, go to Dashboard > Link Roundups > Options.
8. Paste the MailChimp API Key into the appropriate field, then check "Enable MailChimp API Integration".
9. Press "Save Changes".

Next: Creating a template in Mailchimp that your post will use.

## Configuring a MailChimp Template

If you already have a saved template, great! Duplicate it, then pick up below where we insert the template tags into the template.

First, you'll have to create a template.

1. Click on "Templates" in the MailChimp header.
2. Pick a single-column or column-and-sidebar template.
3. Edit the template to your heart's content.
4. Click "Save and Exit".
5. Name your template, then save.

Continue to the next step!

# Creating a MailChimp Campaign

Once you have [saved some links](saving-links.md) and [created the link roundup post](link-roundups.md), you can send the link roundup post to MailChimp as a draft campaign.

In the Link Roundups editor, above the standard WordPress "Publish" button, is a button to Create a MailChimp Campaign. (Docs under construction...)

### Inserting template tags

The Link Roundups Plugin's MailChimp features depend on the presence of specific tags in your email.

You'll have to edit your template to add them, so that they can be filled in with your content when you go to send an email.

1. Click on "Templates" in the MailChimp header.
2. Click "Edit" on the template you want to edit.

Then, edit the template to insert the following tags where they make sense:

#### Required Tags:

If these template tags are not present in your MailChimp template, you will be unable to create a new Argo Links Roundup Email Campaign.

- `*|ROUNDUPLINKS|*` - The actual list of links from the Argo Links Roundup post

#### Optional Tags:

These template tags are not required, but we highly recommend using them in your MailChimp template.

- `*|ROUNDUPTITLE|*` - The Argo Links Roundup post title
- `*|ROUNDUPAUTHOR|*` - The author of the Argo Links Roundup post
- `*|ROUNDUPDATE|*` - The date the Argo Links Roundup post was published
- `*|ROUNDUPPERMALINK|*` - A link back to the original Argo Links Roundup post

## Choosing the template to use

So now you have at least one template saved in MailChimp. Go back to the Link Roundups Options page in Dashboard &raquo; Link Roundups &raquo; Options, and choose your template.

# Setting up Mailchimp Lists
