<?php
/**
*
* @package - Board3portal
* @version $Id: install_install.php 638 2010-03-28 16:27:39Z marc1706 $
* @copyright (c) kevin / saint ( www.board3.de/ ), (c) Ice, (c) nickvergessen ( www.flying-bits.org/ ), (c) redbull254 ( www.digitalfotografie-foren.de ), (c) Christian_N ( www.phpbb-projekt.de )
* @installer based on: phpBB Gallery by nickvergessen, www.flying-bits.org
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @ignore
*/

if (!defined('IN_PHPBB'))
{
	exit;
}
if (!defined('IN_INSTALL'))
{
	exit;
}

if (!empty($setmodules))
{
	if ($this->installed_version || $this->installed_p3p_version)
	{
		return;
	}

	$module[] = array(
		'module_type'		=> 'install',
		'module_title'		=> 'INSTALL',
		'module_filename'	=> substr(basename(__FILE__), 0, -strlen($phpEx)-1),
		'module_order'		=> 10,
		'module_subs'		=> '',
		'module_stages'		=> array('INTRO', 'CREATE_TABLE', 'ADVANCED', 'FINAL'),
		'module_reqs'		=> ''
	);
}

/**
* Installation
* @package install
*/
class install_install extends module
{
	function install_install(&$p_master)
	{
		$this->p_master = &$p_master;
	}

	function main($mode, $sub)
	{
		global $user, $template, $phpbb_root_path, $cache, $phpEx;

		switch ($sub)
		{
			case 'intro':
				$this->page_title = $user->lang['SUB_INTRO'];

				$template->assign_vars(array(
					'TITLE'			=> $user->lang['INSTALL_INTRO'],
					'BODY'			=> $user->lang['INSTALL_INTRO_BODY'],
					'L_SUBMIT'		=> $user->lang['NEXT_STEP'],
					'U_ACTION'		=> $this->p_master->module_url . "?mode=$mode&amp;sub=create_table",
				));

			break;

			case 'create_table':
				$this->load_schema($mode, $sub);
			break;

			case 'advanced':
				$this->obtain_advanced_settings($mode, $sub);

			break;

			case 'final':
				set_portal_config('portal_version', NEWEST_B3P_VERSION, true);
				$cache->purge();

				$template->assign_vars(array(
					'TITLE'		=> $user->lang['INSTALL_CONGRATS'],
					'BODY'		=> sprintf($user->lang['INSTALL_CONGRATS_EXPLAIN'], NEWEST_B3P_VERSION),
					'L_SUBMIT'	=> $user->lang['GOTO_PORTAL'],
					'U_ACTION'	=> append_sid($phpbb_root_path . 'portal.' . $phpEx),
				));


			break;
		}

		$this->tpl_name = 'install_install';
	}

	/**
	* Load the contents of the schema into the database and then alter it based on what has been input during the installation
	*/
	function load_schema($mode, $sub)
	{
		global $db, $user, $template, $phpbb_root_path, $phpEx, $cache;
		include($phpbb_root_path . 'includes/acp/auth.' . $phpEx);

		$this->page_title = $user->lang['STAGE_CREATE_TABLE'];
		$s_hidden_fields = '';

		$dbms_data = get_dbms_infos();
		$db_schema = $dbms_data['db_schema'];
		$delimiter = $dbms_data['delimiter'];

		// Create the tables
		b3p_create_table('phpbb_portal_config', $dbms_data);

		// Set default config
		set_portal_config('portal_welcome_intro', 'Welcome to my community!');
		set_portal_config('portal_max_online_friends', '8');
		set_portal_config('portal_max_most_poster', '8');
		set_portal_config('portal_max_last_member', '8');
		set_portal_config('portal_welcome', '1');
		set_portal_config('portal_links', '1');
		set_portal_config('portal_link_us', '1');
		set_portal_config('portal_clock', '1');
		set_portal_config('portal_random_member', '1');
		set_portal_config('portal_latest_members', '1');
		set_portal_config('portal_top_posters', '1');
		set_portal_config('portal_leaders', '1');
		set_portal_config('portal_advanced_stat', '1');
		set_portal_config('portal_welcome_guest', '1');
		set_portal_config('portal_birthdays', '1');
		set_portal_config('portal_search', '1');
		set_portal_config('portal_friends', '1');
		set_portal_config('portal_whois_online', '1');
		set_portal_config('portal_change_style', '0');
		set_portal_config('portal_main_menu', '1');
		set_portal_config('portal_user_menu', '1');
		set_portal_config('portal_right_column_width', '180');
		set_portal_config('portal_left_column_width', '180');
		set_portal_config('portal_poll_topic', '1');
		set_portal_config('portal_poll_topic_id', '');
		set_portal_config('portal_last_visited_bots_number', '1');
		set_portal_config('portal_load_last_visited_bots', '1');
		set_portal_config('portal_pay_acc', 'your@paypal.com');
		set_portal_config('portal_pay_s_block', '0');
		set_portal_config('portal_pay_c_block', '0');
		set_portal_config('portal_recent', '1');
		set_portal_config('portal_recent_title_limit', '100');
		set_portal_config('portal_max_topics', '10');
		set_portal_config('portal_news_forum', '');
		set_portal_config('portal_news_length', '250');
		set_portal_config('portal_number_of_news', '5');
		set_portal_config('portal_show_all_news', '1');
		set_portal_config('portal_news', '1');
		set_portal_config('portal_news_style', '1');
		set_portal_config('portal_announcements', '1');
		set_portal_config('portal_announcements_style', '0');
		set_portal_config('portal_number_of_announcements', '1');
		set_portal_config('portal_announcements_day', '0');
		set_portal_config('portal_announcements_length', '200');
		set_portal_config('portal_global_announcements_forum', '');
		set_portal_config('portal_wordgraph_word_counts', '0');
		set_portal_config('portal_wordgraph_max_words', '80');
		set_portal_config('portal_wordgraph', '0');
		set_portal_config('portal_wordgraph_ratio', '18');
		set_portal_config('portal_minicalendar', '1');
		set_portal_config('portal_minicalendar_today_color', '#000000');
		set_portal_config('portal_attachments', '1');
		set_portal_config('portal_attachments_number', '8');

		// Added 0.2.1
		set_portal_config('portal_poll_limit', '3');
		set_portal_config('portal_poll_allow_vote', '1');
		set_portal_config('portal_birthdays_ahead', '7');

		// Added 0.2.2
		set_portal_config('portal_attachments_forum_ids', '');

		// Added  0.3.0 A.K.A 1.0.0RC1
		set_portal_config('portal_announcements_permissions', '1');
		set_portal_config('portal_news_permissions', '1');
		set_portal_config('portal_custom_center', '0');
		set_portal_config('portal_custom_small', '0');
		set_portal_config('portal_custom_code_center', '');
		set_portal_config('portal_custom_code_small', '');
		set_portal_config('portal_custom_center_bbcode', '0');
		set_portal_config('portal_custom_small_bbcode', '0');
		set_portal_config('portal_custom_center_headline', 'Headline center box');
		set_portal_config('portal_custom_small_headline', 'Headline small box');
		set_portal_config('portal_forum_index', '0');
		set_portal_config('portal_news_show_last', '0');
		set_portal_config('portal_news_archive', '1');
		set_portal_config('portal_announcements_archive', '1');
		set_portal_config('portal_links_array', 'a:2:{i:1;a:2:{s:4:"text";s:9:"Board3.de";s:3:"url";s:21:"http://www.board3.de/";}i:2;a:2:{s:4:"text";s:9:"phpBB.com";s:3:"url";s:21:"http://www.phpbb.com/";}}');

		// Added 1.0.0RC2
		set_portal_config('portal_leaders_ext', '0');

		// Added 1.0.0RC3
		set_portal_config('portal_show_announcements_replies_views', '1');
		set_portal_config('portal_show_news_replies_views', '1');

		// Added 1.0.3
		set_portal_config('portal_enable', '1');
		set_portal_config('portal_phpbb_menu', '0');
		set_portal_config('portal_poll_hide', '0');
		
		// Added 1.0.4RC1 A.K.A 1.0.4
		set_portal_config('portal_minicalendar_sunday_color', '#FF0000');
		set_portal_config('portal_sunday_first', '1');
		set_portal_config('portal_long_month', '0');
		set_portal_config('portal_attach_max_length', '15');
		set_portal_config('portal_version_check', '1');
		set_portal_config('version_check_time', '0');
		set_portal_config('version_check_version', '0.0.0');
		
		// Added 1.0.5
		set_portal_config('portal_left_column', '1');
		set_portal_config('portal_right_column', '1');
		set_portal_config('portal_news_exclude', '0');
		set_portal_config('portal_announcements_forum_exclude', '0');
		set_portal_config('portal_exclude_forums', '1');
		set_portal_config('portal_recent_forum', '');
		set_portal_config('portal_attachments_forum_exclude', '0');
		set_portal_config('portal_attachments_filetype', '');
		set_portal_config('portal_attachments_exclude', '0');
		set_portal_config('portal_poll_exclude_id', '0');
		
		// Add permissions
		$auth_admin = new auth_admin();
		$auth_admin->acl_add_option(array(
			'local'			=> array(),
			'global'		=> array('a_portal_manage')
		));
		$cache->destroy('acl_options');

		$sql = 'SELECT auth_option_id FROM ' . ACL_OPTIONS_TABLE . "
			WHERE auth_option = 'a_portal_manage'";
		$result = $db->sql_query($sql);
		$auth_option_id = $db->sql_fetchfield('auth_option_id');
		$db->sql_freeresult($result);

		$sql = 'SELECT role_id FROM ' . ACL_ROLES_TABLE . "
			WHERE role_name = 'ROLE_ADMIN_FULL'";
		$result = $db->sql_query($sql);
		$role_id = (int) $db->sql_fetchfield('role_id');
		$db->sql_freeresult($result);

		// Give the wanted role its option
		$roles_data = array(
			'role_id'			=> $role_id,
			'auth_option_id'	=> $auth_option_id,
			'auth_setting'		=> 1,
		);
		
		// First check if the values are already set
		$sql = 'SELECT *
				FROM ' . ACL_ROLES_DATA_TABLE . '
				WHERE auth_option_id = ' . $db->sql_escape($auth_option_id) . '
					AND role_id = ' . $db->sql_escape($role_id);
		$result = $db->sql_query($sql);
		$check_ary = $db->sql_fetchrow($result);
		$db->sql_freeresult();
		
		if(sizeof($check_ary) < 1)
		{
			$sql = 'INSERT INTO ' . ACL_ROLES_DATA_TABLE . ' ' . $db->sql_build_array('INSERT', $roles_data);
			$db->sql_query($sql);
		}

		$submit = $user->lang['NEXT_STEP'];

		$url = $this->p_master->module_url . "?mode=$mode&amp;sub=advanced";

		$template->assign_vars(array(
			'TITLE'		=> $user->lang['STAGE_CREATE_TABLE'],
			'BODY'		=> $user->lang['STAGE_CREATE_TABLE_EXPLAIN'],
			'L_SUBMIT'	=> $submit,
			'S_HIDDEN'	=> '',
			'U_ACTION'	=> $url,
		));
	}

	/**
	* Provide an opportunity to customise some advanced settings during the install
	* in case it is necessary for them to be set to access later
	*/
	function obtain_advanced_settings($mode, $sub)
	{
		global $user, $template, $phpEx, $db;

		$create = request_var('create', '');
		if ($create)
		{
			// Add modules
			$choosen_acp_module = request_var('acp_module', 0);
			if ($choosen_acp_module < 0)
			{
				$acp_mods_tab = array('module_basename' => '',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => 0,	'module_class' => 'acp',	'module_langname'=> 'ACP_CAT_DOT_MODS',	'module_mode' => '',	'module_auth' => '');
				add_module($acp_mods_tab);
				$choosen_acp_module = $db->sql_nextid();
			}
			// ACP
			$acp_portal = array('module_basename' => '',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $choosen_acp_module,	'module_class' => 'acp',	'module_langname'=> 'ACP_PORTAL_INFO',	'module_mode' => '',	'module_auth' => '');
			add_module($acp_portal);
			$acp_module_id = $db->sql_nextid();
			set_portal_config('acp_parent_module', $acp_module_id);

			$acp_portal_general = array('module_basename' => 'portal',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'acp',	'module_langname' => 'ACP_PORTAL_GENERAL_INFO',	'module_mode' => 'general',	'module_auth' => 'acl_a_portal_manage');
			add_module($acp_portal_general);
			$acp_portal_news = array('module_basename'	=> 'portal',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'acp',	'module_langname' => 'ACP_PORTAL_NEWS_INFO',	'module_mode' => 'news',	'module_auth' => 'acl_a_portal_manage');
			add_module($acp_portal_news);
			$acp_portal_announcements = array('module_basename' => 'portal',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'acp',	'module_langname' => 'ACP_PORTAL_ANNOUNCEMENTS_INFO',	'module_mode' => 'announcements',	'module_auth' => 'acl_a_portal_manage');
			add_module($acp_portal_announcements);
			$acp_portal_welcome = array('module_basename'	=> 'portal',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'acp',	'module_langname' => 'ACP_PORTAL_WELCOME_INFO',	'module_mode' => 'welcome',	'module_auth' => 'acl_a_portal_manage');
			add_module($acp_portal_welcome);
			$acp_portal_recent = array('module_basename' => 'portal',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'acp',	'module_langname' => 'ACP_PORTAL_RECENT_INFO',	'module_mode' => 'recent',	'module_auth' => 'acl_a_portal_manage');
			add_module($acp_portal_recent);
			$acp_portal_wordgraph = array('module_basename'	=> 'portal',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'acp',	'module_langname' => 'ACP_PORTAL_WORDGRAPH_INFO',	'module_mode' => 'wordgraph',	'module_auth' => 'acl_a_portal_manage');
			add_module($acp_portal_wordgraph);
			$acp_portal_paypal = array('module_basename' => 'portal',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'acp',	'module_langname' => 'ACP_PORTAL_PAYPAL_INFO',	'module_mode' => 'paypal',	'module_auth' => 'acl_a_portal_manage');
			add_module($acp_portal_paypal);
			$acp_portal_attachments = array('module_basename'	=> 'portal',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'acp',	'module_langname' => 'ACP_PORTAL_ATTACHMENTS_INFO',	'module_mode' => 'attachments',	'module_auth' => 'acl_a_portal_manage');
			add_module($acp_portal_attachments);
			$acp_portal_members = array('module_basename'	=> 'portal',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'acp',	'module_langname' => 'ACP_PORTAL_MEMBERS_INFO',	'module_mode' => 'members',	'module_auth' => 'acl_a_portal_manage');
			add_module($acp_portal_members);
			$acp_portal_polls = array('module_basename' => 'portal',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'acp',	'module_langname' => 'ACP_PORTAL_POLLS_INFO',	'module_mode' => 'polls',	'module_auth' => 'acl_a_portal_manage');
			add_module($acp_portal_polls);
			$acp_portal_bots = array('module_basename'	=> 'portal',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'acp',	'module_langname' => 'ACP_PORTAL_BOTS_INFO',	'module_mode' => 'bots',	'module_auth' => 'acl_a_portal_manage');
			add_module($acp_portal_bots);
			$acp_portal_poster = array('module_basename' => 'portal',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'acp',	'module_langname' => 'ACP_PORTAL_POSTER_INFO',	'module_mode' => 'poster',	'module_auth' => 'acl_a_portal_manage');
			add_module($acp_portal_poster);
			$acp_portal_minicalendar = array('module_basename'	=> 'portal',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'acp',	'module_langname' => 'ACP_PORTAL_MINICALENDAR_INFO',	'module_mode' => 'minicalendar',	'module_auth' => 'acl_a_portal_manage');
			add_module($acp_portal_minicalendar);
			$acp_portal_customblock = array('module_basename' => 'portal',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'acp',	'module_langname' => 'ACP_PORTAL_CUSTOMBLOCK_INFO',	'module_mode' => 'customblock',	'module_auth' => 'acl_a_portal_manage');
			add_module($acp_portal_customblock);
			$acp_portal_linkblock = array('module_basename'	=> 'portal',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'acp',	'module_langname' => 'ACP_PORTAL_LINKS_INFO',	'module_mode' => 'links',	'module_auth' => 'acl_a_portal_manage');
			add_module($acp_portal_linkblock);
			$acp_portal_friends = array('module_basename' => 'portal',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'acp',	'module_langname' => 'ACP_PORTAL_FRIENDS_INFO',	'module_mode' => 'friends',	'module_auth' => 'acl_a_portal_manage');
			add_module($acp_portal_friends);
			$acp_portal_birthdays = array('module_basename' => 'portal',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'acp',	'module_langname' => 'ACP_PORTAL_BIRTHDAYS_INFO',	'module_mode' => 'birthdays',	'module_auth' => 'acl_a_portal_manage');
			add_module($acp_portal_birthdays);

			$s_hidden_fields = '';
			$url = $this->p_master->module_url . "?mode=$mode&amp;sub=final";
		}
		else
		{
			$data = array(
				'acp_module'		=> 31,
			);

			foreach ($this->portal_config_options as $config_key => $vars)
			{
				if (!is_array($vars) && strpos($config_key, 'legend') === false)
				{
					continue;
				}

				if (strpos($config_key, 'legend') !== false)
				{
					$template->assign_block_vars('options', array(
						'S_LEGEND'		=> true,
						'LEGEND'		=> $user->lang[$vars])
					);

					continue;
				}

				$options = isset($vars['options']) ? $vars['options'] : '';
				$template->assign_block_vars('options', array(
					'KEY'			=> $config_key,
					'TITLE'			=> $user->lang[$vars['lang']],
					'S_EXPLAIN'		=> $vars['explain'],
					'S_LEGEND'		=> false,
					'TITLE_EXPLAIN'	=> ($vars['explain']) ? $user->lang[$vars['lang'] . '_EXPLAIN'] : '',
					'CONTENT'		=> $this->p_master->input_field($config_key, $vars['type'], $data[$config_key], $options),
					)
				);
			}
			$s_hidden_fields = '<input type="hidden" name="create" value="true" />';
			$url = $this->p_master->module_url . "?mode=$mode&amp;sub=advanced";
		}

		$submit = $user->lang['NEXT_STEP'];

		$template->assign_vars(array(
			'TITLE'		=> $user->lang['STAGE_ADVANCED'],
			'BODY'		=> (!$create) ? $user->lang['STAGE_ADVANCED_EXPLAIN'] : $user->lang['STAGE_ADVANCED_SUCCESSFUL'],
			'L_SUBMIT'	=> $submit,
			'S_HIDDEN'	=> $s_hidden_fields,
			'U_ACTION'	=> $url,
		));
	}

	/**
	* The information below will be used to build the input fields presented to the user
	*/
	var $portal_config_options = array(
		'legend1'				=> 'MODULES_PARENT_SELECT',
		'acp_module'			=> array('lang' => 'MODULES_SELECT_4ACP', 'type' => 'select', 'options' => 'module_select(\'acp\', 31, \'ACP_CAT_DOT_MODS\')', 'explain' => false),
	);
}

?>