# tzchz/TOTPhp_Advanced
This is the Advanced version of [tzchz/TOTPhp](https://github.com/tzchz/TOTPhp) with enhanced security, encrypting raw 2FA Keys with the card you tap with your mobile device.
### Before we start
Look up the [Basic version](https://github.com/tzchz/TOTPhp) for more details about setting up your site.
### Advanced Encryption
The Basic version, which fetches your TOTP code without downloading the raw Key onto your local device, would be good for personal use but will not encrypt the Key on your server end.

While this[Advanced ] version adds security to your server storage, there are extra requirements to get your codes, e.g. an IC Card as well as a device with NFC tag.

We suggest you comparing the two different versions before making a choice.

Note that you may use separated Cards for different Accounts stored in this version.