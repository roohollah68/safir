---
layout: default
title: Attributes Extension
description: The AttributesExtension allows HTML attributes to be added from within the document.
redirect_from: /extensions/attributes/
---

# Attributes

The `AttributesExtension` allows HTML attributes to be added from within the document.

**Security warning:** Allowing untrusted users to inject arbitrary HTML attributes could lead to XSS vulnerabilities, styling issues, or other problems. Consider [disabling unsafe links](/2.7/security/#unsafe-links), [configuring allowed attributes](#configuration), and/or [using additional filtering](/2.7/security/#additional-filtering).

## Attribute Syntax

The basic syntax was inspired by [Kramdown](http://kramdown.gettalong.org/syntax.html#attribute-list-definitions)'s Attribute Lists feature.

You can assign any attribute to a block-level element. Just directly prepend or follow the block with a block inline attribute list.
That consists of a left curly brace, optionally followed by a colon, the attribute definitions and a right curly brace:

```markdown
> A nice blockquote
{: title="Blockquote title"}
```

This results in the following output:

```html
<blockquote title="Blockquote title">
<p>A nice blockquote</p>
</blockquote>
```

CSS-selector-style declarations can be used to set the `id` and `class` attributes:

```markdown
{#id .class}
## Header
```

Output:

```html
<h2 class="class" id="id">Header</h2>
```

As with a block-level element you can assign any attribute to a span-level elements using a span inline attribute list,
that has the same syntax and must immediately follow the span-level element:

```markdown
This is *red*{style="color: red"}.
```

Output:

```html
<p>This is <em style="color: red">red</em>.</p>
```

## Installation

This extension is bundled with `league/commonmark`. This library can be installed via Composer:

```bash
composer require league/commonmark
```

See the [installation](/2.7/installation/) section for more details.

## Usage

Configure your `Environment` as usual and simply add the `AttributesExtension`:

```php
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;

// Example custom configuration
$config = [
    'attributes' => [
        'allow' => ['id', 'class', 'align'],
    ],
];

// Configure the Environment with all the CommonMark parsers/renderers
$environment = new Environment($config);
$environment->addExtension(new CommonMarkCoreExtension());

// Add this extension
$environment->addExtension(new AttributesExtension());

// Instantiate the converter engine and start converting some Markdown!
$converter = new MarkdownConverter($environment);
echo $converter->convert('# Hello World! {.article-title}');
```

## Configuration

As of version 2.7.0, this extension can be configured by providing a `attributes` array with nested configuration options.

### `allow`

An array of allowed attributes. An empty array `[]` (default) allows virtually all attributes.

**Note:** Attributes starting with `on` (e.g. `onclick` or `onerror`) are capable of executing JavaScript code and are therefore **never allowed by default**. You must explicitly add them to the `allow` list if you want to use them.
