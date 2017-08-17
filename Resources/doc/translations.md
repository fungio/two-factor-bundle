Translations
============

TwoFAS/TwoFactorBundle uses **Symfony Translation** component to provide built-in support for translating pages.

The elements of the interface are translated using the TwoFASTwoFactorBundle domain.
The rest of the elements are translated by default using the `messages` (for flashes) or `validation` (for form errors) domain.

Before translating your backend, make sure that the translator service is enabled in the application (projects based on the Symfony Standard Edition have it disabled by default):

```yaml
framework:
    translator: { fallbacks: ["%locale%"] }
```

If you want to translate pages into your language add files:

- messages.locale.yml
- TwoFASTwoFactorBundle.locale.yml
- validators.locale.yml

to `app/Resources/TwoFASTwoFactorBundle/translations` and fill keys (from `en` locale) in your own language.

[**<< Overriding templates**](templates.md) | [**Index**](index.md) | [**Full configuration reference >>**](configuration-reference.md)