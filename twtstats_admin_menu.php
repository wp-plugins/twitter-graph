<?php
/*  Copyright 2011  Lumolink  (email : Jussi Räsänen <jussi.rasanen@lumolink.com>)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

    if($_POST['twtstats_hidden'] == 'Y') {
        $twtstatsusername = $_POST['twtstatsusername'];
        update_option('twtstatsusername', $twtstatsusername);
    }
    else {
        $twtstatsusername = get_option('twtstatsusername');
    }
?><div class="wrap" id="twitterstats">
	<?php echo "<h2>" . __( 'Twitter Graph', 'twtstats_trdom' ) . "</h2>"; ?>

	<form name="twtstats_form" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
		<input type="hidden" name="twtstats_hidden" value="Y">
		<p><?php _e("Your Twitter username: " ); ?><input type="text" name="twtstatsusername" value="<?php echo $twtstatsusername; ?>" size="20"></p>

		<p class="submit">
		<input type="submit" name="Submit" value="<?php _e('Save settings', 'twtstats_trdom' ) ?>" />
		</p>
	</form>
</div>
