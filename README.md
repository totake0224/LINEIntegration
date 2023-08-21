# LINEIntegration
LINE Integration for FreeScout

1,Receive Message from LINE <br>
 this module handle text,stamp,image,movie,audio,location message.<br>
 stamp and image is showed inline.movie and audio is showed as attachment file.
 location message is showed with url of google map.
 
2,Send Message to LINE <br>
 this module only handle non html message.attachment file is added as link at downward of message body.<br>
 message with inline image is send wrong way. please do not use inline image.

3,usage.
 at first you have to sign in to LINE DEVELOPER Console and make channnel for LINE messaging API.
 after that you could get channnel ID,channnel secret channnel TOKEN.<br>
 you have to set these value at freescout mailbox's LINE setting menu.
 at this menu you could get webhook url for the mailbox. you have to set the url as webhook url at LINE DEVELOPER Console.

4,Limitation.<br>
 I. one mailbox has only one LINE messaging bot.<br>
 II. first message is never appeared.(normally first message is add friend notice.except you delete talk thread.)
 III. may be a lot of bug or something.
 
5.Installation<br>
 clone in freescout/Modules and change user and owner.(or translation never reflected).
 after that activate from freescout module menu.
 setting menu is in mailbox setting.