{*
  The links of the module's left menu, from menu.ini [Leftmenu_syndication]. Shared by parts/syndication/menu.tpl of
  every admin design, so the links are written once.
*}
{include uri='design:parts/ini_menu.tpl' ini_section='Leftmenu_syndication' i18n_hash=hash(
    'syndication',        'Syndication'|i18n( 'extension/syndication' ),
    'feeds',              'Feeds'|i18n( 'extension/syndication' ),
    'imports',            'Imports'|i18n( 'extension/syndication' ),
    'sources',            'Feed Sources'|i18n( 'extension/syndication' )
)}

{* the "Import and export" list of the transfer registry (Exponential 6.0.15 and later; nothing on an older kernel) *}
{if ezini_hasvariable( 'Service_transfer.registry', 'Class', 'kernelservices.ini' )}{include uri='design:transfer/left_menu.tpl' current='transfer.syndication'}{/if}
