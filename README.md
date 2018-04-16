# Flattable

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/KroneMultimedia/plugin-flattable/badges/quality-score.png?b=beta)](https://scrutinizer-ci.com/g/KroneMultimedia/plugin-flattable/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/KroneMultimedia/plugin-flattable/badges/coverage.png?b=beta)](https://scrutinizer-ci.com/g/KroneMultimedia/plugin-flattable/?branch=beta) [![Build Status](https://travis-ci.org/KroneMultimedia/plugin-flattable.svg?branch=master)](https://travis-ci.org/KroneMultimedia/plugin-flattable)




Provides a simple API to publish a post including post meta to a simple flattable.



|         | Features  |
----------|-----------|
| :innocent:   | Configuration works out of the box  for all post types |
| :sweat_smile:| Built for large WordPress installations |
| :thumbsup:   | Clean  API |
| :crown: | Supported and battle tested @krone.at |
| :octocat: | 100% free and open source |



> The Plugin itself does nothing, it only provides actions and filters
> that one can use to create a flattable



# Demo

Imagine you have a custom post type called `article`

## The demo below produces a table `wp_flattable_articles` that looks like


```sql
+---------------------+---------------+------+-----+---------------------+----------------+
| Field               | Type          | Null | Key | Default             | Extra          |
+---------------------+---------------+------+-----+---------------------+----------------+
| id                  | int(12)       | NO   | PRI | NULL                | auto_increment |
| post_id             | int(12)       | YES  | UNI | NULL                |                |
| post_type           | varchar(100)  | YES  |     | NULL                |                |
| post_date           | datetime      | NO   |     | 0000-00-00 00:00:00 |                |
| post_date_gmt       | datetime      | NO   |     | 0000-00-00 00:00:00 |                |
| post_status         | varchar(20)   | NO   |     | publish             |                |
| post_modified       | datetime      | NO   |     | 0000-00-00 00:00:00 |                |
| post_modified_gmt   | datetime      | NO   |     | 0000-00-00 00:00:00 |                |
| frontend_time_gmt   | datetime      | NO   |     | 0000-00-00 00:00:00 |                |
| offline_time        | datetime      | NO   |     | 0000-00-00 00:00:00 |                |
| article_format      | varchar(100)  | YES  |     | NULL                |                |
| post_title          | varchar(1024) | YES  |     | NULL                |                |
| post_author         | int(12)       | YES  |     | NULL                |                |
| comment_status      | varchar(100)  | YES  |     | NULL                |                |
| first_published_gmt | datetime      | NO   |     | 0000-00-00 00:00:00 |                |
+---------------------+---------------+------+-----+---------------------+----------------+

```



## Register Filters

```php
  add_filter('krn_flattable_enabled_article', [$this, "flattable_enabled"], 10, 2);
  add_filter('krn_flattable_columns_article', [$this, "flattable_columns"], 10, 2);
  add_filter('krn_flattable_values_article', [$this, "flattable_values"], 10, 2);
  add_filter('krn_flattable_pre_write_article', [$this, 'flattable_pre_write'], 10, 2);
  add_filter('krn_flattable_pre_delete_article', [$this, 'flattable_pre_delete'], 10, 2);
```

## API + samples


### `krn_flattable_enabled_$POSTTYPE`

| Input |     |
|-------|-----|
|  `$state` | post state (e.g: `publish`, `future`) |
|  `$postObject` | WPPost Object | 

Return: `true`  or `false` if you'd want to use flattable functions

```php
function flattable_enabled($state, $postObject) {
    return true;
}
```

### `krn_flattable_columns_$POSTTYPE`

| Input |     |
|-------|-----|
|  `$state` | post state (e.g: `publish`, `future`) |
|  `$postObject` | WPPost Object | 

#### Return


| Return |   |
|--------|---|
| `array`| Return an array of column definitions |

```php
function flattable_columns($columns, $postObject)
{
  return [
    ["column" => "post_date", "type" =>  "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'", "printf" => "%s"],
    ["column" => "post_date_gmt", "type" =>  "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'", "printf" => "%s"],
    ["column" => "post_status", "type" =>  "varchar(20) NOT NULL DEFAULT 'publish'", "printf" => "%s"],
    ["column" => "post_modified", "type" =>  "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'", "printf" => "%s"],
    ["column" => "post_modified_gmt", "type" =>  "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'", "printf" => "%s"],
    ["column" => "frontend_time_gmt", "type" =>  "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'", "printf" => "%s"],
    ["column" => "offline_time", "type" =>  "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'", "printf" => "%s"],
    ["column" => "article_format", "type" =>  "varchar(100)", "printf" => "%s"],
    ["column" => "post_title", "type" =>  "varchar(1024)", "printf" => "%s"],
    ["column" => "post_author", "type" =>  "int(12)", "printf" => "%d"],
    ["column" => "comment_status", "type" =>  "varchar(100)", "printf" => "%s"],
    ["column" => "first_published_gmt", "type" =>  "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'", "printf" => "%s"],
 ];
}

```

### `krn_flattable_values_$POSTTYPE`

| Input |     |
|-------|-----|
|  `$postObject` | WPPost Object | 

#### Return


| Return |   |
|--------|---|
| `array`| Return array with values |

```php
function flattable_values($data, $postObject) {
  $frontend_time_gmt = get_post_meta($postObject->ID, 'frontend_time_gmt', true);
  $article_format = get_post_meta($postObject->ID, 'article_format', true);
  $offline_time = get_post_meta($postObject->ID, 'offline_time', true);
  $first_published_gmt = get_post_meta($postObject->ID, 'first_published_gmt', true);
  return [
    "post_date" => $postObject->post_date,
    "post_date_gmt" => $postObject->post_date_gmt,
    "post_status" => $postObject->post_status,
    "post_modified" => $postObject->post_modified,
    'post_modified_gmt' => $postObject->post_modified_gmt,
    'frontend_time_gmt' => $frontend_time_gmt,
    'offline_time' => $offline_time,
    'article_format' => $article_format,
    'post_title' => $postObject->post_title,
    'comment_status' => $postObject->comment_status,
    'post_author' => $postObject->post_author,
    'first_published_gmt' => $first_published_gmt
  ];
}
```


### `krn_flattable_pre_write_$POSTTYPE`

| Input |     |
|-------|-----|
|  `$postObject` | WPPost Object | 


is fired right before the insert into the flattable is done.
here you can do for example:
  - create another table
  - do something else


### `krn_flattable_pre_delete_$POSTTYPE`

| Input |     |
|-------|-----|
|  `$postObject` | WPPost Object | 


is fired right before the delete in  the flattable is executed.
here you can do for example:
  - cleanup other tables
  - do something else

