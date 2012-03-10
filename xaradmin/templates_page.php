<?php
/**
 * Publications Module
 *
 * @package modules
 * @subpackage publications module
 * @category Third Party Xaraya Module
 * @version 2.0.0
 * @copyright (C) 2011 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

sys::import('modules.dynamicdata.class.objects.master');

function publications_admin_templates_page($args)
{
    if (!xarSecurityCheck('AdminPublications')) return;

    extract($args);

    if (!xarVarFetch('confirm',       'int',    $confirm,            0,  XARVAR_NOT_REQUIRED)) {return;}
    if (!xarVarFetch('ptid',           'id',    $data['ptid'],       0,  XARVAR_NOT_REQUIRED)) {return;}
    if (!xarVarFetch('itemid',         'id',    $data['itemid'],     0,  XARVAR_NOT_REQUIRED)) {return;}
    if (!xarVarFetch('file',          'str',    $data['file'],       'summary',  XARVAR_NOT_REQUIRED)) {return;}
    if (!xarVarFetch('source_data',   'str',    $data['source_data'],       '',  XARVAR_NOT_REQUIRED)) {return;}

    if (empty($data['itemid']) || empty($data['ptid'])) return xarResponse::NotFound();
    
    $pubtypeobject = DataObjectMaster::getObject(array('name' => 'publications_types'));
    $pubtypeobject->getItem(array('itemid' => $data['ptid']));
    $pubtype = explode('_',$pubtypeobject->properties['name']->value);
    $pubtype = isset($pubtype[1]) ? $pubtype[1] : $pubtype[0];
    
    $data['object'] = DataObjectMaster::getObject(array('name' => $pubtypeobject->properties['name']->value));

    $basepath = sys::code() . "modules/publications/xartemplates/objects/" . $pubtype;
    $sourcefile = $basepath . "/" . $data['file'] . "_" . $data['itemid'] . ".xt";
    $overridepath = "themes/" . xarModVars::get('themes', 'default_theme') . "/modules/publications/objects/" . $pubtype;
    $overridefile = $overridepath . "/" . $data['file'] . "-" . $data['itemid'] . ".xt";

    // Let the template know what kind of file this is
    if (file_exists($overridefile)) {
        $data['filetype'] = 'theme';
        $filepath = $overridefile;
    } else {
        $data['filetype'] = 'module';
        $filepath = $sourcefile;
    }
    
    if ($confirm && !empty($data['source_data'])) {
        xarMod::apiFunc('publications', 'admin', 'write_file', array('file' => $sourcefile, 'data' => $data['source_data']));
    }
    
    $data['source_data'] = trim(xarMod::apiFunc('publications', 'admin', 'read_file', array('file' => $filepath)));

    // Initialize the template
    if (empty($data['source_data'])) {
        $data['source_data'] = '<xar:template xmlns:xar="http://xaraya.com/2004/blocklayout">';
        $data['source_data'] .= "\n";
        $data['source_data'] .= "\n" . '</xar:template>';
    }

    $data['files'] = array(
        array('id' => 'summary', 'name' => 'summary display'),
        array('id' => 'detail',  'name' => 'detail display'),
    );
    return $data;
}
?>