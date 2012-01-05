Hooks and extention for Desjardins payment processor integration in CiviCRM.

It integrations with the payment method "without redirection" (process, not notify),
therefore you will need an SSL certificate on your donation page.


INSTALLATION:
------------

Enable this module in Drupal, then enable the extention in CiviCRM.

The module also requires the "curl" PHP library. Under Debian, apt-get install php5-curl.

IMPORTANT:
* This module overrides your extention directory. (maybe we ought to find a better solution?)
* The 'extensions' subdirectory must be writable by the web server (this is a civicrm requirement).


MORE INFORMATION:
----------------

https://github.com/mlutfy/CiviCRM-Desjardins

(C) 2011 Mathieu Lutfy <mathieu@bidon.ca>

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

