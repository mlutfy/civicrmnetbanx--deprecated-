Netbanx (Optimal Payments) payment processor integration for CiviCRM.

This is the new payment gateway (since 2012) used by Desjardins in Quebec.
It should work for merchants using Netbanx directly as well.

It integrates with the payment method "without redirection" (process, not notify),
therefore you will need an SSL certificate for your payment page. In Netbanx terms,
this is a "hosted payment page - autonomous".

Your site must be conform to PCI-DSS requirements.

INSTALLATION:
------------

See the INSTALL.txt file.

TODO:
----

* Remove mentions to Desjardins, replace by Netbanx.
* Make logo customizable (currently defaults to Desjardins).
* Support recurrent billing (ex: monthly donations).
* UI to configure the civicrmdesjardins_tos_text and civicrmdesjardins_tos_url variables.
  (you can use the 'variable' module to configure them)
* More hook_requirements to have a clearer checklist of what needs to be done before having
  a site validated by Desjardins (based on the auto-evaluation).
* Respect the CiviCRM settings for accepted cards (amex, mastercard) - this is already managed
  via civicrm/admin/options/accept_creditcard?group=accept_creditcard&reset=1
  i.e. do not show the Amex/MC logo if the card is not accepted.
* Propose a patch to CiviCRM so that we have a standard way of displaying the receipt in the
  ThankYou.tpl, so that we do not need to systematically override the template.
* Implement AVS and 3D-secure (Not a priority, to be honest. Rarely required in a CiviCRM context).

MORE INFORMATION:
----------------

Technical information about the payment gateway:
http://support.optimalpayments.com/docapi.asp
http://support.optimalpayments.com/test_environment.asp

To open an account, contact Optimal Payments sales via their website.
If you are with Desjardins, contact Desjardins merchant services.

More information about this code:
https://github.com/mlutfy/civicrmnetbanx

CREDITS:
-------

(C) 2011-2012 Mathieu Lutfy <mathieu@bidon.ca>
http://www.bidon.ca/en/about

Thanks to Henrique Recidive for his commerce_netbanx module, which helped
to understand the Netbanx spec.
http://drupal.org/project/commerce_netbanx

This module is in no way affiliated, endorsed or supported by Desjardins,
Netbanx/Optimal Solutions or Visa/Mastercard.


LICENSE:
-------

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along
    with this program; if not, write to the Free Software Foundation, Inc.,
    51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

See LICENSE.txt for more information.

