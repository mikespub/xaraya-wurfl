<?php
/**
 * Articles module
 *
 * @package modules
 * @copyright (C) 2002-2006 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Articles Module
 * @link http://xaraya.com/index.php/release/151.html
 * @author mikespub
 */
/**
 * check security for a particular article
 *
 * @param $args['mask'] the requested security mask
 *
 * @param $args['article'] the article array (if already retrieved)
 * @param $args['aid'] the article ID (if known, and article array not
                       already retrieved)
 * @param $args['authorid'] the user ID of the author (if not already included)
 * @param $args['ptid'] the publication type ID (if not already included)
 * @param $args['cids'] array of additional required category checks
 *
 * @return bool true if OK, false if not OK
 */
function articles_userapi_checksecurity($args)
{
    // Get arguments from argument array
    extract($args);

    // Compatibility mode with old API params - remove later
    if (isset($access) && !isset($mask)) {
        switch ($access) {
            case ACCESS_OVERVIEW:
                $mask = 'ViewArticles';
                break;
            case ACCESS_READ:
                $mask = 'ReadArticles';
                break;
            case ACCESS_COMMENT:
                $mask = 'SubmitArticles';
                break;
            case ACCESS_EDIT:
                $mask = 'EditArticles';
                break;
            case ACCESS_DELETE:
                $mask = 'DeleteArticles';
                break;
            case ACCESS_ADMIN:
                $mask = 'AdminArticles';
                break;
            default:
                $mask = '';
        }
    }

    if (empty($mask)) {
        return false;
    }

    // Get article information
    if (!isset($article) && !empty($aid) && $mask != 'SubmitArticles') {
        $article = xarModAPIFunc('articles',
                                'user',
                                'get',
                                array('aid' => $aid,
                                      'withcids' => true));
        if ($article == false) {
            return false;
        }
    }
    if (empty($aid) && isset($article['aid'])) {
        $aid = $article['aid'];
    }
    if (!isset($aid)) {
        $aid = '';
    }

    // Get author ID
    if (isset($article['authorid']) && empty($authorid)) {
        $authorid = $article['authorid'];
    }

    // Get status
    if (isset($article['status']) && !isset($status)) {
        $status = $article['status'];
    }
    if (empty($status)) {
        $status = 0;
    }
    // reject reading access to unapproved articles
    if ($status < 2 && ($mask == 'ViewArticles' || $mask == 'ReadArticles')) {
        return false;
    }

    // Get publication type ID
    if (isset($article['pubtypeid'])) {
        if (!isset($ptid)) {
            $ptid = $article['pubtypeid'];
        } elseif ($ptid != $article['pubtypeid'] && $mask != 'EditArticles') {
            return false;
        }
    }

    // Get root categories for this publication type
    if (!empty($ptid)) {
        $rootcats = xarModAPIFunc('categories','user','getallcatbases',array('module' => 'articles', 'itemtype' => $ptid));
    } else {
        $ptid = null;
    }
    if (!isset($rootcids)) {
    // TODO: handle cross-pubtype views better
        $rootcats = xarModAPIFunc('categories','user','getallcatbases',array('module' => 'articles'));
    }

    // Get category information for this article
    if (!isset($article['cids']) && !empty($aid)) {
        if (!xarModAPILoad('categories', 'user')) return;
        $info = xarMod::getBaseInfo('articles');
        $sysid = $info['systemid'];
        $articlecids = xarModAPIFunc('categories',
                                    'user',
                                    'getlinks',
                                    array('iids' => Array($aid),
                                          'itemtype' => $ptid,
                                          'modid' => $sysid,
                                          'reverse' => 0
                                         )
                                   );
        if (is_array($articlecids) && count($articlecids) > 0) {
            $article['cids'] = array_keys($articlecids);
        }
    }
    if (!isset($article['cids'])) {
        $article['cids'] = array();
    }

    if (!isset($cids)) {
        $cids = array();
    }

    $jointcids = array();
/* TODO: forget about parent/root cids for now
    foreach ($rootcids as $cid) {
        $jointcids[$cid] = 1;
    }
*/
    foreach ($article['cids'] as $cid) {
        $jointcids[$cid] = 1;
    }
    // FIXME: the line within the foreach is known to give an illegal offset error, not sure how to properly
    // fix it. Only seen on using xmlrpc and bloggerapi.
    foreach ($cids as $cid) {
        if (empty($cid) || !is_numeric($cid)) continue;
        $jointcids[$cid] = 1;
    }

// TODO 1: find a way to combine checking over several categories
// TODO 2: find a way to check parent categories for privileges too

// TODO 3: find a way to specify current user in privileges too
// TODO 4: find a way to check parent groups of authors for privileges too ??

    if (empty($ptid)) {
        $ptid = 'All';
    }
    if (count($jointcids) == 0) {
        $jointcids['All'] = 1;
    }
// TODO: check for anonymous articles
    if (!isset($authorid)) {
        $authorid = 'All';
    }
    if (empty($aid)) {
        $aid = 'All';
    }

    // Loop over all categories and check the different combinations
    $result = false;
    foreach (array_keys($jointcids) as $cid) {
// TODO: do we want all-or-nothing access here, or is one access enough ?
        if (xarSecurityCheck($mask,0,'Article',"$ptid:$cid:$authorid:$aid")) {
            $result = true;
        }
    }
    return $result;
}

?>
