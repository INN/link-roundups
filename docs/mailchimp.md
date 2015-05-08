# Creating a MailChimp campaign

Once you have [saved some links](saving-links) and [created the link roundup post](link-roundups), you can send the link roundup post to MailChimp as a draft campaign.

In the Link Roundups editor, above the standard WordPress "Publish" button, is a tktk

# Setting up a MailChimp templates

In MailChimp, create a new template. Then, edit the template to insert the following tags where they make sense:

Required:

### Required:

If these template tags are not present in your MailChimp template, you will be unable to create a new Argo Links Roundup Email Campaign.

- `*|ROUNDUPLINKS|*` - The actual list of links from the Argo Links Roundup post

### Optional:

These template tags are not required, but we highly recommend using them in your MailChimp template.

- `*|ROUNDUPTITLE|*` - The Argo Links Roundup post title
- `*|ROUNDUPAUTHOR|*` - The author of the Argo Links Roundup post
- `*|ROUNDUPDATE|*` - The date the Argo Links Roundup post was published
- `*|ROUNDUPPERMALINK|*` - A link back to the original Argo Links Roundup post
