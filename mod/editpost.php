<?php

require_once('acl_selectors.php');

function editpost_content(&$a) {

	$o = '';

	if(! local_user()) {
		notice( t('Permission denied.') . EOL);
		return;
	}

	$post_id = (($a->argc > 1) ? intval($a->argv[1]) : 0);

	if(! $post_id) {
		notice( t('Item not found') . EOL);
		return;
	}

	$r = q("SELECT * FROM `item` WHERE `id` = %d AND `uid` = %d LIMIT 1",
		intval($post_id),
		intval(local_user())
	);

	if(! count($r)) {
		notice( t('Item not found') . EOL);
		return;
	}


	$o .= '<h2>' . t('Edit post') . '</h2>';

	$tpl = load_view_file('view/jot-header.tpl');
	
	$a->page['htmlhead'] .= replace_macros($tpl, array(
		'$baseurl' => $a->get_baseurl(),
		'$geotag' => $geotag,
		'$nickname' => $a->user['nickname']
	));


	$tpl = load_view_file("view/jot.tpl");
		
	if(($group) || (is_array($a->user) && ((strlen($a->user['allow_cid'])) || (strlen($a->user['allow_gid'])) || (strlen($a->user['deny_cid'])) || (strlen($a->user['deny_gid'])))))
		$lockstate = 'lock';
	else
		$lockstate = 'unlock';

	$celeb = ((($a->user['page-flags'] == PAGE_SOAPBOX) || ($a->user['page-flags'] == PAGE_COMMUNITY)) ? true : false);

	$jotplugins = '';
	$jotnets = '';

	$mail_disabled = ((function_exists('imap_open') && (! get_config('system','imap_disabled'))) ? 0 : 1);

	$mail_enabled = false;
	$pubmail_enabled = false;

	if(! $mail_disabled) {
		$r = q("SELECT * FROM `mailacct` WHERE `uid` = %d AND `server` != '' LIMIT 1",
			intval(local_user())
		);
		if(count($r)) {
			$mail_enabled = true;
			if(intval($r[0]['pubmail']))
				$pubmail_enabled = true;
		}
	}

	if($mail_enabled) {
       $selected = (($pubmail_enabled) ? ' checked="checked" ' : '');
		$jotnets .= '<div class="profile-jot-net"><input type="checkbox" name="pubmail_enable"' . $selected . 'value="1" /> '
          	. t("Post to Email") . '</div>';
	}
					


	call_hooks('jot_tool', $jotplugins);
	call_hooks('jot_networks', $jotnets);

	$tpl = replace_macros($tpl,array('$jotplugins' => $jotplugins));	

	$o .= replace_macros($tpl,array(
		'$return_path' => $_SESSION['return_url'],
		'$action' => 'item',
		'$share' => t('Edit'),
		'$upload' => t('Upload photo'),
		'$weblink' => t('Insert web link'),
		'$youtube' => t('Insert YouTube video'),
		'$video' => t('Insert Vorbis [.ogg] video'),
		'$audio' => t('Insert Vorbis [.ogg] audio'),
		'$setloc' => t('Set your location'),
		'$noloc' => t('Clear browser location'),
		'$wait' => t('Please wait'),
		'$permset' => t('Permission settings'),
		'$content' => $r[0]['body'],
		'$post_id' => $post_id,
		'$baseurl' => $a->get_baseurl(),
		'$defloc' => $a->user['default-location'],
		'$visitor' => 'none',
		'$emailcc' => t('CC: email addresses'),
		'$jotnets' => $jotnets,
		'$emtitle' => t('Example: bob@example.com, mary@example.com'),
		'$lockstate' => $lockstate,
		'$acl' => '', // populate_acl((($group) ? $group_acl : $a->user), $celeb),
		'$bang' => (($group) ? '!' : ''),
		'$profile_uid' => $_SESSION['uid']
	));


	return $o;

}


