<?php
/**
 * Articles module
 *
 * @package modules
 * @copyright (C) copyright-placeholder
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Articles Module
 * @link http://xaraya.com/index.php/release/151.html
 * @author mikespub
 */
/**
 * the main user function
 */
function articles_user_main($args)
{
    return xarModFunc('articles','user','view',$args);
// TODO: make this configurable someday ?
    // redirect to default view (with news articles)
    //xarResponseRedirect(xarModURL('articles', 'user', 'view'));
    //return;
}

?>
