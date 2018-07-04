# wp_enqueue_less

wp_enqueue_less provides a function to enqueue less stylesheets in WordPress.

## Install

### Composer

Composer is the best way to install wp_enqueue_less so you get updates in the future easily.

```
composer require ed-itsolutions/wp_enqueue_less
```

and then in your `functions.php` of `plugin.php`

```php
require_once('vendor/autoload.php');
```

### Manually

Download a copy of wp_enqueue_less.php and require it in your theme/plugin.

## Usage

In your normal `wp_enqueue_scripts` action simply call `wp_enqueue_less`

`wp_enqueue_less` takes 3 arguments.

 - _key_ - The key name to use for this stylesheet.
 - _filePath_ - The on disk path to the .less file.
 - _variables_ - A key->value array of variables to be passed to the less compiler.

```php
add_action('wp_enqueue_scripts', function(){
  wp_enqueue_style('theme-stylesheet', get_stylesheet_uri());

  wp_enqueue_less('theme-main', get_template_directory() . '/less/main.less', array(
    'main-color' => '#99bbff' // becomes @main-color in your less stylesheet.
  ));
});
```

Thats it!

wp_enqueue_less will:

 - Compile this less file and write the output to `/wp-content/uploads/less/key-hash.css` (this can be changed with the filter `wp_enqueue_less_css_dir`).
 - Record the current hashes of all the less files used and the variables into the database.
 - On the next call if none of the hashes have changed it will skip parsing.
 - On a daily basis it will clean out its directory of everything but the current hash version of the stylesheet.