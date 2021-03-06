<?php
/* For licensing terms, see /license.txt */
/**
 * @package chamilo.admin
 * @todo use formvalidator
 */

////require_once '../inc/global.inc.php';

$xajax = new xajax();
$xajax->registerFunction('search_courses');

// setting the section (for the tabs)
$this_section = SECTION_PLATFORM_ADMIN;

// Access restrictions
api_protect_admin_script(true);

// setting breadcrumbs
$interbreadcrumb[] = array('url' => 'index.php', 'name' => get_lang('PlatformAdmin'));
$interbreadcrumb[] = array('url' => api_get_path(WEB_CODE_PATH).'session/session_list.php', 'name' => get_lang('SessionList'));

// Database Table Definitions
$tbl_session = Database::get_main_table(TABLE_MAIN_SESSION);

// setting the name of the tool
$tool_name = get_lang('SubscribeSessionsToCategory');
$id_session = isset($_GET['id_session']) ? intval($_GET['id_session']) : null;

$add_type = 'multiple';
if (isset($_GET['add_type']) && $_GET['add_type'] != '') {
    $add_type = Security::remove_XSS($_REQUEST['add_type']);
}

if (!api_is_platform_admin() && !api_is_session_admin()) {
    $sql = 'SELECT session_admin_id FROM '.Database :: get_main_table(TABLE_MAIN_SESSION).' WHERE id=' . $id_session;
    $rs = Database::query($sql);
    if (Database::result($rs, 0, 0) != $_user['user_id']) {
        api_not_allowed(true);
    }
}

$xajax->processRequests();
$htmlHeadXtra[] = $xajax->getJavascript('../inc/lib/xajax/');
$htmlHeadXtra[] = '<script>
function add_course_to_session (code, content) {
	document.getElementById("course_to_add").value = "";
	document.getElementById("ajax_list_courses_single").innerHTML = "";
	destination = document.getElementById("destination");
	for (i=0;i<destination.length;i++) {
		if(destination.options[i].text == content) {
				return false;
		}
	}
	destination.options[destination.length] = new Option(content,code);
	destination.selectedIndex = -1;
	sortOptions(destination.options);
}
function send() {
	if (document.formulaire.CategorySessionId.value!=0) {
		document.formulaire.formSent.value=0;
		document.formulaire.submit();
	}
}
function remove_item(origin)
{
	for(var i = 0 ; i<origin.options.length ; i++) {
		if(origin.options[i].selected) {
			origin.options[i]=null;
			i = i-1;
		}
	}
}
</script>';

$formSent = 0;
$errorMsg = $firstLetterCourse = $firstLetterSession = '';
$CourseList = $SessionList = array();
$courses = $sessions = array();
$Categoryid = isset($_POST['CategorySessionId']) ? intval($_POST['CategorySessionId']) : null;

if (isset($_POST['formSent']) && $_POST['formSent']) {
    $formSent = $_POST['formSent'];
    $SessionCategoryList = $_POST['SessionCategoryList'];

    if ($Categoryid != 0 && count($SessionCategoryList) > 0) {
        $session_id = join(',', $SessionCategoryList);
        $sql = "UPDATE $tbl_session SET session_category_id = $Categoryid WHERE id in ($session_id) ";
        Database::query($sql);
        header('Location: add_many_session_to_category.php?id_category=' . $Categoryid . '&msg=ok');
        exit;
    } else {
        header('Location: add_many_session_to_category.php?msg=error');
        exit;
    }
}

if (isset($_GET['id_category'])) {
    $Categoryid = intval($_GET['id_category']);
}

if (isset($_GET['msg']) && $_GET['msg'] == 'error') {
    $errorMsg = get_lang('MsgErrorSessionCategory');
}

if (isset($_GET['msg']) && $_GET['msg'] == 'ok') {
    $OkMsg = get_lang('SessionCategoryUpdate');
}

$page = isset($_GET['page']) ? Security::remove_XSS($_GET['page']) : null;

Display::display_header($tool_name);

$where = '';
$rows_category_session = array();
if ((isset($_POST['CategorySessionId']) && $_POST['formSent'] == 0) || isset($_GET['id_category'])) {

    $where = 'WHERE session_category_id !=' . $Categoryid;
    $sql = 'SELECT id, name  FROM ' . $tbl_session . ' WHERE session_category_id =' . $Categoryid . ' ORDER BY name';
    $result = Database::query($sql);
    $rows_category_session = Database::store_result($result);
}

$rows_session_category = SessionManager::get_all_session_category();
if (empty($rows_session_category)) {
    Display::display_warning_message(get_lang('YouNeedToAddASessionCategoryFirst'));
    Display::display_footer();
    exit;
}

if (api_get_multiple_access_url()) {
    $table_access_url_rel_session= Database::get_main_table(TABLE_MAIN_ACCESS_URL_REL_SESSION);
    $access_url_id = api_get_current_access_url_id();
    $sql = "SELECT s.id, s.name  FROM $tbl_session s INNER JOIN $table_access_url_rel_session u ON s.id = u.session_id $where AND u.access_url_id = $access_url_id ORDER BY name";
} else {
    $sql = "SELECT id, name  FROM $tbl_session $where ORDER BY name";
}
$result=Database::query($sql);
$rows_session = Database::store_result($result);
?>
<form name="formulaire" method="post"
      action="<?php echo api_get_self(); ?>?page=<?php echo $page;
      if (!empty($_GET['add'])) {
          echo '&add=true';
      } ?>" style="margin:0px;">
    <?php echo '<legend>' . $tool_name . '</legend>'; ?>
    <input type="hidden" name="formSent" value="1"/>
    <?php
    if (!empty($errorMsg)) {
        Display::display_error_message($errorMsg); //main API
    }

    if (!empty($OkMsg)) {
        Display::display_confirmation_message($OkMsg); //main API
    }

    /*
     *
     * The a/b/c Filter is not a priority
     *
     * <td width="45%" align="center">
     <?php echo get_lang('FirstLetterCourse'); ?> :
         <select name="firstLetterCourse" onchange = "xajax_search_courses(this.value,'multiple')">
          <option value="%">--</option>
          <?php
          echo Display :: get_alphabet_options();
          echo Display :: get_numeric_options(0,9,'');
          ?>
         </select>
    </td>
     */
    ?>
    <table border="0" cellpadding="5" cellspacing="0" width="100%" align="center">
        <tr>
            <td align="left"></td>
            <td align="left"></td>
            <td align="center">
                <b><?php echo get_lang('SessionCategoryName') ?> :</b><br/>
                <select name="CategorySessionId" style="width: 320px;" onchange="javascript:send();">
                    <option value="0"></option>
                    <?php
                    if (!empty($rows_session_category)) {
                        foreach ($rows_session_category as $category) {
                            if ($category['id'] == $Categoryid) {
                                echo '<option value="' . $category['id'] . '" selected>' . $category['name'] . '</option>';
                            } else {
                                echo '<option value="' . $category['id'] . '">' . $category['name'] . '</option>';
                            }
                        }
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td width="45%" align="center"><b><?php echo get_lang('SessionListInPlatform') ?> :</b></td>
            <td width="10%">&nbsp;</td>
            <td align="center" width="45%"><b><?php echo get_lang('SessionListInCategory') ?> :</b></td>
        </tr>

        <?php if ($add_type == 'multiple') { ?>
            <tr>
                <td>&nbsp;</td>
            </tr>
        <?php } ?>
        <tr>
            <td width="45%" align="center">
                <div id="ajax_list_courses_multiple">
                    <select id="origin" name="NoSessionCategoryList[]" multiple="multiple" size="20"
                            style="width:320px;">
                        <?php
                        foreach ($rows_session as $enreg) {
                            ?>
                            <option value="<?php echo $enreg['id']; ?>" <?php echo 'title="' . htmlspecialchars(
                                    $enreg['name'],
                                    ENT_QUOTES
                                ) . '"';
                            if (in_array(
                                $enreg['id'],
                                $CourseList
                            )
                            ) {
                                echo 'selected="selected"';
                            } ?>><?php echo $enreg['name']; ?></option>
                        <?php } ?>
                    </select></div>
                <?php unset($nosessionCourses); ?>
            </td>
            <td width="10%" valign="middle" align="center">
                <button class="arrowr" type="button"
                        onclick="moveItem(document.getElementById('origin'), document.getElementById('destination'))"
                        onclick="moveItem(document.getElementById('origin'), document.getElementById('destination'))"></button>
                <br/><br/>
                <button class="arrowl" type="button"
                        onclick="moveItem(document.getElementById('destination'), document.getElementById('origin'))"
                        onclick="moveItem(document.getElementById('destination'), document.getElementById('origin'))"></button>
                <br/><br/><br/><br/><br/><br/>
                <?php
                echo '<button class="save" type="button" value="" onclick="valide()" >' . get_lang('SubscribeSessionsToCategory').'</button>';
                ?>
            </td>
            <td width="45%" align="center">
                <select id='destination' name="SessionCategoryList[]" multiple="multiple" size="20" style="width:320px;">
                <?php
                foreach ($rows_category_session as $enreg) {
                    ?>
                    <option value="<?php echo $enreg['id']; ?>" <?php echo 'title="' . htmlspecialchars(
                            $enreg['name'],
                            ENT_QUOTES
                        ) . '"';
                    if (in_array(
                        $enreg['id'],
                        $CourseList
                    )
                    ) {
                        echo 'selected="selected"';
                    } ?>><?php echo $enreg['name']; ?></option>
                <?php } ?>
                </select>
            </td>
        </tr>
    </table>
</form>
<script>
    function valide() {
        var options = document.getElementById('destination').options;
        for (i = 0; i < options.length; i++)
            options[i].selected = true;
        document.forms.formulaire.submit();
    }
</script>
