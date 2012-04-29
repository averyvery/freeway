# Freeway - Simple Routing for EE

Freeway adds routes (a common web application concept) to EE. You set the routes in your Freeway settings like so, and just separate them with linebreaks:

	journals/{{user}} => blogs/users/{{user}}

If you set a route like this, visitors should be able to visit "journals/admin", but their request will be interpreted by EE as "blogs/users/admin/". EE will load the blogs template group and the user template. Segments one, two, and three will be blogs, users, and admin. Additionally, "admin" will be available in the template as <code>{freeway_user}</code>. So, that's fun.

## Why?

[See Issue #1](https://github.com/averyvery/freeway/issues/1). Routes are a valuable concept because they separate your URLs from your data. They make more sense in an MVC application, but in EE, they provide added power and flexibility around your URLs.

## Usage

- Install Freeway in your third_party folder
- Enable it on the Addon -> Extensions page
- Copy freeway_routes.php.sample to your config directory, and remove the '.sample'.
- Define your routes in config/freeway_routes.php:

	return array(
		'/blog/{{username}}/{{category}}' => '/blog/view/category/{{category}}'
	)

	In this case, a URL like "blog/davery/css" will be treated, in EE, as "blog/view/category/css".
	Several variables will be available in the template:

		{segment_1} - blog
		{segment_2} - view
		{segment_3} - category
		{segment_4} - css

		{freeway_1} - blog
		{freeway_2} - davery
		{freeway_3} - css
		{freeway_4} - (blank)

		{freeway_username} - davery
		{freeway_category} - css

## MSM

Freeway will assume all routes are global, unless you namespace them with your site names:

	return array(
		'default_site' => Array(
			'foo' => 'bar'
		),
		'french_site' => Array(
			'fou' => 'bar'
		)
	)

## Debugging

Use the {freeway_info} var to found out more about how Freeway is parsing the URL.

	{freeway_info}

You should be able to click around and watch the segments and variables update.

## Testing

Freeway uses [Testee](http://experienceinternet.co.uk/software/testee/) for unit testing. To run tests:

- Install [Testee](http://experienceinternet.co.uk/software/testee/)
- Symlink the test.freeway.php from freeway/testee to testee/tests:

	ln -s ~/Sites/mysite/third_party/freeway/test.freeway.php ~/Sites/mysite/third_party/testee/tests/test.freeway.php

## Future Ideas

- Route partial segments through, like /foo{{bar}}/ => /category-{{bar}}/
- Run common queries like category_id on tokens before passing them on to new ones (example: {{category from=cat_name to=cat_id}} would take the cat name, but return te id

