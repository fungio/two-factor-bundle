Overriding templates
====================

Fungio/TwoFactorBundle provides a few default basic templates that you probably need to override.
To do this, Symfony provides a built-in way to [override the templates themselves](https://symfony.com/doc/current/templating/overriding.html).
All templates (except the main template) have ``css`` classes so you might only need to write styles for them.

It is highly recommended that you override the ``Resources/views/layout.html.twig`` template
so that the pages provided by the TwoFactorBundle have a similar look and feel to the rest of your application.


Here is the default layout.html.twig provided by the TwoFactorBundle:

```twig

    {% extends "::base.html.twig" %}

    {% block stylesheets %}
        {{ parent() }}
    {% endblock %}
    {% block javascripts %}
        {{ parent() }}
        <script src="{{ asset('bundles/fungiotwofactor/js/main.js')|fileMTime }}"></script>
        <script src="{{ asset('bundles/fosjsrouting/js/router.js')|fileMTime }}"></script>
        <script src="{{ asset('js/fos_js_routes.js')|fileMTime }}"></script>
    {% endblock %}

    {% block title %}{% endblock %}
    {% block body %}{% endblock %}
```

As you can see, it’s pretty basic and doesn't really have much structure, without any `css` styles, so after installation you will see white, plain html pages.

If you want, you can include our sample styles located at `Resources/public/css/style.css`.
 
We include some `javascript` files: the `main.js` is our script and it’s required for the bundle to work properly.
Other files are provided by **FosJSRoutingBundle**, so if you are using this bundle now, you probably have these scripts, and you can remove them from `layout.twig.html`.
 
If you are using **AsseticBundle**, you can use *javascripts* block to include files.
We use own filter *fileMTime* to add unix timestamp to the end of the file name to prevent browser cache javascript files,
if you are using AsseticBundle, we recommend to turn on [versioning](http://symfony.com/doc/current/reference/configuration/framework.html#reference-framework-assets-version).
 
To override the layout template located at `Resources/views/layout.html.twig` in the TwoFactorBundle directory, you would place your new layout template at `app/Resources/FungioTwoFactorBundle/views/layout.html.twig`.
 
The pattern for overriding templates in this way is to create a folder with the name of the bundle class in the app/Resources directory.
Then add your new template to this folder, preserving the directory structure from the original bundle.
But first check the structure of templates, maybe you may only need to write `css styles` to existing classes.

After overriding a template, you must clear the cache for the override to take effect, even in a development environment.

> **Note**
>
> If you dont't use **FOSJsRoutingBundle** or you are using plain javascript or another JS framework like **Angular**,
> you have to write your own script based on ``main.js`` and replace main.js by your script in ``layout.html.twig``.


We are using standard Symfony flash messages (success, info, warning):

```twig
{% for label, flashes in app.session.flashbag.all %}
    {% for flash in flashes %}
        <div class="alert alert-{{ label }}">
            {{ flash }}
        </div>
    {% endfor %}
{% endfor %}
```
Make sure that you have a similar section in your `app/Resources/views/base.html.twig` file.
 
You probably want to place 2FAS in your menu,so that users can enter and configure their accounts to use the second factor.
You have to show this menu only for authenticated users or for a specified role.

To do this you can use our twig function:

```twig
{% if canRenderFungio('IS_AUTHENTICATED_REMEMBERED') %}
    <li><a href="{{ path('fungio_index') }}">2FAS</a></li>
{% endif %}
```

Check out our demo application on [Github](https://github.com/fungio/two-factor-demo), you can find there overwritten templates (simple bootstrap example).

[**<< Configuration**](configuration.md) | [**Index**](index.md) | [**Translations >>**](translations.md)