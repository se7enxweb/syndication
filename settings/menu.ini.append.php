<?php /* #?ini charset="utf-8"?

[NavigationPart]
Part[ezsyndicationnavigationpart]=Syndication

[TopAdminMenu]
Tabs[]=syndication

[Topmenu_syndication]
NavigationPartIdentifier=ezsyndicationnavigationpart
Name=Syndication
Tooltip=Export parts of the content tree as feeds, import the feeds of other sites
URL[]
URL[default]=syndication/menu
Enabled[]
Enabled[default]=true
Enabled[browse]=false
Enabled[edit]=false
Shown[]
Shown[default]=true
Shown[navigation]=true
Shown[browse]=true
PolicyList[]=syndication/menu

[Leftmenu_syndication]
Name=Syndication
Links[]
Links[syndication]=syndication/menu
Links[feeds]=syndication/list
Links[imports]=syndication/import_list
Links[sources]=syndication/add_feed_source
LinkNames[]
LinkNames[syndication]=Syndication
LinkNames[feeds]=Feeds
LinkNames[imports]=Imports
LinkNames[sources]=Feed Sources
Enabled[]
Enabled[default]=true
Enabled[edit]=false
Enabled[browse]=false
PolicyList_syndication[]=syndication/menu
PolicyList_feeds[]=syndication/view_export
PolicyList_imports[]=syndication/view_export
PolicyList_sources[]=syndication/edit_export

*/ ?>
