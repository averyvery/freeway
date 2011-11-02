# Freeway - Simple Routing for EE

Freeway adds routes (a common web application concept) to EE. You set the routes in your Freeway settings like so, and just separate them with linebreaks:

	journals/{{user}} => blogs/users/{{user}}

If you set a route like this, visitors should be able to visit "journals/admin", but their request will be interpreted by EE as "blogs/users/admin/". Additionally, the user will be available in the template as {freeway_user}. So, that's fun.

# Usage

- Install Freeway in your third_party folder
- Enable it on the Addon -> Extensions page
- Routes
	A route looks like this:
		/blog/{{username}}/{{category}} => /blog/category/{{category}}

	In this case, a URL like "blog/davery/css" will be parsed, in EE, as "blog/category/css"
	Several variables will be available in the template:

		{freeway_username} - davery
		{freeway_category} - css
		{freeway_1} - blog
		{freeway_2} - davery
		{freeway_3} - css
		{freeway_4+} - (blank)
		{freeway_info} - debug info from Freeway
- Template variables:
	- {freeway_[varname]} - the value of {{varname}} in the URL match
	- {freeway_1}, {freeway_2} - "original" segments, the one you see in your browser bar
	- {segment_1}, {segment_2} - "parsed" segments, the ones EE is sent
	- {freeway_info} - debug info from Freeway

# Future Ideas

- Route partial segments like /foo{{bar}}/ => /category-{{bar}}/
- Run common queries like category_id on tokens before passing them on to new ones (example: {{category from=cat_name to=cat_id}} would take the cat name, but return te id 

