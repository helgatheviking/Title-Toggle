#Title Toggle
Contributors: helgatheviking     
Donate link: https://inspirepay.com/pay/helgatheviking     
Tags: title, the_title     
Requires at least: 4.2     
Tested up to: 4.2     
Stable tag: 0.0.1     
License: GPLv2 or later     
License URI: http://www.gnu.org/licenses/gpl-2.0.html     

## Description

Easily hide titles from any post or page.

I needed a quick way to let clients hide page titles on certain pages that were getting custom content. Eventually I found an existing plugin, but it was a bit out of date, and I wanted to add some quick edit stuff. Plus, it was a great exercise for using more Grunt. 

## Usage

To hide a title, check the checkbox in the publish metabox and update the post.

![Check to hide](https://raw.github.com/helgatheviking/title-toggle/master/screenshot1.png)

In the edit overview, a checkmark means the title is displaying. Click on the checkmark to toggle the display. An X mark means the title is being hidden. A checkbox is also available if editing via quick edit. 

![Click on the checkmark to hide the title for that post](https://raw.github.com/helgatheviking/title-toggle/master/screenshot2.png)

## FAQ

### Seeing an empty space where the title used to be

If your theme is using this style of markup in the single content templates:

`
<h1 class="entry-title"><?php the_title(); ?></h1>
`

You will end up with an empy `<h1>` tag in the end markup. Depending on CSS styles this might leave a blank space where the title was. To avoid this you can do one of two things:

Convert your template markup like so:
`
<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
`

Or, if you can't change your templates, you can hide the titles via CSS since Title Toggle will add a `.no-title` class to the article's `post_class`. (nb: following assumes your title's class is `.entry-title`)

`
.no-title .entry-title { display: none; }
`




