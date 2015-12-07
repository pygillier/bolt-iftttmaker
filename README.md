Bolt IFTTT Maker channel extension
==================================

This extension enables a link between a Bolt instance and the [Maker channel](https://ifttt.com/maker) 
from [IFTTT](https://ifttt.com).

On publication of selected contents, a message will be send to IFTTT as a trigger. You'll be then able to chain any
action you want like sending a tweet or publishing link to your facebook.

A message will be sent when a content is saved and one of the two following conditions is met : 

1. Timed publication.
2. "Live" saving of a content and its status is updated to "published".

__*Attention :*__ updating content's status back and forth from "published" will send a message each time ! It's due to 
a limitation of Bolt which doesn't provide a "first publication" trigger (yet)

Installation
------------

Using Bolt's extensions manager, install this extension by its name "pygillier/iftttmaker".
After install, you'll need to configure the extension by editing its configuration file.

Enable channel in IFTTT
---------------------

Go to https://ifttt.com/maker and connect the channel to your account. You'll receive a key which will be used to 
authenticate your messages.

Configuration options
---------------------

All options are located in the file  iftttmaker.pygillier.yml in extensions config directory.

* channel_key : The key IFTTT gave you when you connected your account.
* event_name : a freeform string which will be sent along the message, you'll use it when defining a recipe with 
this channel in IFTTT.
* content_types : A list of content types on which a message will be sent. No message will be sent on others contents.

Usage
-----

When a content is published, a message will be sent to the channel. The following values from content will be sent to 
the channel : 

* Content's title will be sent as "value1"
* Permalink to content will be sent as "value2"

You'll be able to use them in subsequents action.