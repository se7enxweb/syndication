<?php /* #?ini charset="utf-8"?

# Syndication in Exponential's transfer registry (kernel 6.0.15 and later, settings/kernelservices.ini; the kernel's
# doc/eng-us/features/6.0/transfer-registry.md). The kernel lists the tool in the "Which import or export tool do I
# need?" help page, the "Looking for…?" boxes of the other tools, the "Import and export" lists of the Setup menu and
# the left menus, an item's Import/Export menu ("Copy to another site…") and the administration's search. Older
# kernels ignore this file.

[Service_transfer.syndication]
Class=expKernelTransferTool
Factory=expKernelTransferTool::fromSettings
Arguments[]=transfer.syndication
Scope=worker
Tags[]=transfer.tool
Description=Keep folders in step between Exponential sites: one site offers them as feeds, the others import them and update the same items again and again.
Name=Syndication
Direction=both
Scopes[]=subtree
Scopes[]=object
Formats[]=XML
EntryURL=syndication/menu
Permission=syndication/menu
HelpPage=ext/syndication/README.md
RoundTrip[]=transfer.syndication
RoundTrip[]=transfer.package
Keywords[]=copy to another site
Keywords[]=keep in sync
Keywords[]=mirror
Keywords[]=other installation
Keywords[]=auf eine andere Website kopieren
Keywords[]=abgleichen
Actions[]=node|export|Copy to another site…|syndication/list|syndication/edit_export
Weight=30

*/ ?>
