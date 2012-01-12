Hooks and extention for Desjardins payment processor integration in CiviCRM.

It integrations with the payment method "without redirection" (process, not notify),
therefore you will need an SSL certificate on your donation page.


INSTALLATION:
------------

See the INSTALL.txt file.

TODO:
----

* UI to configure the civicrmdesjardins_tos_text and civicrmdesjardins_tos_url variables.
* More hook_requirements to have a clearer checklist of what needs to be done before having
  a site validated by Desjardins (based on the auto-evaluation).
* Respect the CiviCRM settings for accepted cards (amex, mastercard) - this is already managed
  via civicrm/admin/options/accept_creditcard?group=accept_creditcard&reset=1
  i.e. do not show the Amex/MC logo if the card is not accepted.

MORE INFORMATION:
----------------

https://github.com/mlutfy/CiviCRM-Desjardins

(C) 2011-2012 Mathieu Lutfy <mathieu@bidon.ca>

This module is in no way affiliated, endorsed or supported by Desjardins or Visa Desjardins.

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

