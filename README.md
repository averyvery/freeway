# Freeway - Simple Routing for EE

Freeway adds routes (a common web application concept) to EE. You set the routes in your Freeway settings like so, and just separate them with linebreaks:

	journals/{{user}} => blogs/users/{{user}}

If you set a route like this, visitors should be able to visit "journals/admin", but their request will be interpreted by EE as "blogs/users/admin/". EE will load the blogs template group and the user template. Segments one, two, and three will be blogs, users, and admin. Additionally, "admin" will be available in the template as <code>{freeway_user}</code>. So, that's fun.

## Why?

[See Issue #1](https://github.com/averyvery/freeway/issues/1). Routes are a valuable concept because they separate your URLs from your data. They make more sense in an MVC application, but in EE, they provide added power and flexibility around your URLs.

## Usage

- Install Freeway in your third_party folder
- Enable it on the Addon -> Extensions page
- Set routes on the extension settings page. A route looks like this:

		/blog/{{username}}/{{category}} => /blog/category/{{category}}

	In this case, a URL like "blog/davery/css" will be treated, in EE, as "blog/category/css".
	Several variables will be available in the template:

		{freeway_username} - davery
		{freeway_category} - css
		{freeway_1} - blog
		{freeway_2} - davery
		{freeway_3} - css
		{freeway_4+} - (blank)
		{freeway_info} - debug info from Freeway
- Template variables:
	- <code>{freeway_[varname]}</code> - the value of <code>{{varname}}</code> in the URL match
	- <code>{freeway_1}</code>, <code>{freeway_2}</code> - "original" segments, the one you see in your browser bar
	- <code>{segment_1}</code>, <code>{segment_2}</code> - "parsed" segments, the ones EE is sent
	- <code>{freeway_info}</code> - debug info from Freeway

## Example Settings/Template

Installed Freeway, but still don't get it? Try the following settings:

	journals => blogs/
	journals/{{user}} => blogs/users/{{user}}
	product/{{product_id}}/{{action}} => catalog/product_lookup/id/{{product_id}}/{{action}}

Then, set the following code in your index template:

	<p>EE parses the current URI as:
		<strong>
			/ {segment_1}
			{if segment_2}/ {segment_2}{/if}
			{if segment_3}/ {segment_3}{/if}
			{if segment_4}/ {segment_4}{/if}
			{if segment_5}/ {segment_5}{/if}
			{if segment_6}/ {segment_6}{/if}
		</strong>
	</p>

	<p>The original URI has been stored:
		<strong>
			/ {freeway_1}
			{if freeway_2}/ {freeway_2}{/if}
			{if freeway_3}/ {freeway_3}{/if}
			{if freeway_4}/ {freeway_4}{/if}
			{if freeway_5}/ {freeway_5}{/if}
			{if freeway_6}/ {freeway_6}{/if}
		</strong>
	</p>

	<p>And some variables have been saved:<br>
		<strong>
		freeway_user: {freeway_user}<br>
		freeway_product_id: {freeway_product_id}<br>
		freeway_action: {freeway_action}
		</strong>
	</p>

	<p>
		<a href="/product/42423423/buy/" target="_blank">/product/42423423/buy/</a><br>
		<a href="/journals/" target="_blank">/journals</a><br>
		<a href="/journals/jimmy/" target="_blank">/journals/jimmy</a><br>
	</p>

	<hr>
	{freeway_info}

You should be able to click around and watch the segments and variables update.

## Future Ideas

- Route partial segments through, like /foo{{bar}}/ => /category-{{bar}}/
- Run common queries like category_id on tokens before passing them on to new ones (example: {{category from=cat_name to=cat_id}} would take the cat name, but return te id 

