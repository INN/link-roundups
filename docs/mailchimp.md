# Using Link Roundups Mailchimp Integration

Using the Mailchimp API, Link Roundups can send Roundups as newsletters via a MailChimp account.

## Prerequisites: Setup a Template and List in MailChimp

You'll need to create a template and a list if you don't already have them in MailChimp.

## Getting an API key from MailChimp

In order to use the MailChimp features of Link Roundups, you'll need to sign up for an API key from MailChimp. To do that:

1. Log in to MailChimp.
2. Click on your account in the upper-right corner. In the drop-down menu, click "Account".
3. Click on "Extras", then "API keys".
4. Click "Create A Key". A new key will appear in the list.
5. Click the "Label" field of your new API key. Name it after the site you're using Argo Links on. For example: "example.com argo links"
6. Copy the API key. This will be a long string of characters from 0-9 and a-f.
7. On your website, go to **Dashboard > Link Roundups > Options**.
8. Paste the MailChimp API Key into the appropriate field, then check "Enable MailChimp API Integration".
9. Press "Save Changes".

![Link Roundups MailChimp API settings](./img/link-roundups-mailchimp-integration.png)

## Creating a template in the MailChimp dashboard

You'll need a MailChimp template created and configured for use with Link Roundups.

There are many options for creating MailChimp templates, but as a simple way of getting started:

1. Click on "Templates" in the MailChimp header.
2. Pick a single-column or column-and-sidebar template.
3. Edit the template being sure to use [Link Roundups template tags](#inserting-template-tags) in your template to place your content.
4. Click "Save and Exit".
5. Name your template, then save.

### Inserting template tags

This plugin's MailChimp features depend on the presence of specific tags in your MailChimp template.

You'll have to edit your template to add them, so that they can be replaced with your content when you go to send a campaign.

1. Click on "Templates" in the MailChimp header.
2. Click "Edit" on the template you want to edit.

Then, edit the template to insert the following tags where you want them to appear:

#### Required Tags:

If these template tags are not present in your MailChimp template, you will be unable to create a new Argo Links Roundup Email Campaign.

- `*|ROUNDUPLINKS|*` - The actual list of links from the Argo Links Roundup post

#### Optional Tags:

These template tags are not required, but you may wish to use them in your template.

- `*|ROUNDUPTITLE|*` - The Link Roundup post title
- `*|ROUNDUPAUTHOR|*` - The author of the Link Roundup post
- `*|ROUNDUPDATE|*` - The date the Links Roundu post was published
- `*|ROUNDUPPERMALINK|*` - A link back to the original Link Roundup post

## Choosing the MailChimp Template and List to use

So now you have at least one template saved in MailChimp, and we're assuming you also have a list of subscribers for your newsletter. Go back to the Link Roundups Options page in **Link Roundups > Options**, and choose your template, and which MailChiimp List you want to use:

![Link Roundups MailChimp API settings](./img/link-roundups-options-mailchimp-2.png)

# Creating a MailChimp Campaign

Once you have [saved some links](saving-links.md) and [created a link roundup post](link-roundups.md), you can send the link roundup post to MailChimp as a draft campaign.

In the Link Roundups editor, above the standard WordPress "Publish" button you'll find a button to "Create a MailChimp Campaign."

![Create MailChimp Campaign button in the post editor](./img/link-roundup-mailchimp-button.png)

When you click "Create a MailChimp Campaign" you will be asked to confirm the action. The Link Roundups plugin will contact MailChimp to create the campaign and, upon success, present you with a link to finish editing the campaign in the MailChimp dashboard.