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
 * return the path for a short URL to xarModURL for this module
 *
 * @param $args the function and arguments passed to xarModURL
 * @return string path to be added to index.php for a short URL, or empty if failed
 */
function articles_userapi_encode_shorturl($args)
{
    // Get arguments from argument array
    extract($args);

    // check if we have something to work with
    if (!isset($func)) {
        return;
    }

    // Get the article settings for this publication type
    //Sometimes $settings can be set but $string can return empty eg importing a pubtype
    //Let's make provision for this
    $string=''; //initialize
    if (!empty($ptid)) {
        $string = xarModGetVar('articles', 'settings.'.$ptid);
    } else {
        $string = xarModGetVar('articles', 'settings');
    }
    if (!empty($string)) {
        $settings = unserialize($string);
    }

    // check if we want to encode URLs using their titles rather then their ID
    $encodeUsingTitle= empty($settings['usetitleforurl']) ? 0 : $settings['usetitleforurl'];


    // Coming from categories etc.
    if (!empty($objectid)) {
        $id = $objectid;
    }
    if (!empty($itemtype)) {
        $ptid = $itemtype;
    }
    if (empty($catid) && !empty($cids) && count($cids) > 0) {
        if (!empty($andcids)) {
            $catid = join('+',$cids);
        } else {
            $catid = join('-',$cids);
        }
    }

    // make sure you don't pass the following variables as arguments too

    // default path is empty -> no short URL
    $path = '';
    // if we want to add some common arguments as URL parameters below
    $join = '?';
    // we can't rely on xarModGetName() here (yet) !
    $module = 'articles';

    $pubtypes = xarModAPIFunc('articles','user','getpubtypes');

    // specify some short URLs relevant to your module
    if ($func == 'main') {
        $alias = xarModGetAlias('frontpage');
        if ($module == $alias) {
            // OK, we can use a 'fake' module name here
            $path = '/frontpage/';
        } else {
            $path = '/' . $module . '/';
        }
    } elseif ($func == 'view') {
// TODO: review logic of possible combinations
        if (isset($authorid) && $authorid > 0) {
            if (isset($ptid) && isset($pubtypes[$ptid])) {
                $alias = xarModGetAlias($pubtypes[$ptid]['name']);
                if ($module == $alias) {
                    // OK, we can use a 'fake' module name here
                    $path = '/' . $pubtypes[$ptid]['name'] . '/';
                } else {
                    $path = '/' . $module . '/' . $pubtypes[$ptid]['name'] . '/';
                }
            } else {
                $path = '/' . $module . '/';
            }
            $path .= xarML('by_author') . '/' . $authorid;
        } elseif (!empty($catid)) {
            if (isset($ptid) && isset($pubtypes[$ptid])) {
                if (isset($bycat)) {
                    $path = '/' . $module . '/c' . $catid
                            . '/' . $pubtypes[$ptid]['name'] . '/';
                } else {
                    $alias = xarModGetAlias($pubtypes[$ptid]['name']);
                    if ($module == $alias) {
                        // OK, we can use a 'fake' module name here
                        $path = '/' . $pubtypes[$ptid]['name']
                                . '/c' . $catid . '/';
                    } else {
                        $path = '/' . $module . '/' . $pubtypes[$ptid]['name']
                                . '/c' . $catid . '/';
                    }
                }
            } else {
                $path = '/' . $module . '/c' . $catid . '/';
            }
/*
// perhaps someday, with a convertor to 7-bit ASCII or something...
            // use a cache to avoid re-querying for each URL in the same cat
            static $catcache = array();
            $cid = $cids[0];
            if (xarModAPILoad('categories','user')) {
                if (isset($catcache[$cid])) {
                    $cat = $catcache[$cid];
                } else {
                    $cat = xarModAPIFunc('categories','user','getcatinfo',
                                        array('cid' => $cid));
                    // put the category in cache
                    $catcache[$cid] = $cat;
                }
                if (!empty($cat) && !empty($cat['name'])) {
                    // use the category name as part of the path here
                    $name = preg_replace('/\s+/','_',$cat['name']);
                    $name = strtolower($name);
                    $path = '/' . $module . '/c/' . rawurlencode($name) .'/';
                }
            }
*/
        } elseif (isset($ptid) && isset($pubtypes[$ptid])) {
            $alias = xarModGetAlias($pubtypes[$ptid]['name']);
            if ($module == $alias) {
                // OK, we can use a 'fake' module name here
                $path = '/' . $pubtypes[$ptid]['name'] . '/';
            } else {
                $path = '/' . $module . '/' . $pubtypes[$ptid]['name'] . '/';
            }
        } else {
            $alias = xarModGetAlias('frontpage');
            if ($module == $alias) {
                // OK, we can use a 'fake' module name here
                $path = '/frontpage/';
            } else {
                $path = '/' . $module . '/';
            }
        }
    } elseif ($func == 'display' && isset($id)) {
        if (isset($ptid) && isset($pubtypes[$ptid]))
        {

            $alias = xarModGetAlias($pubtypes[$ptid]['name']);
            if ($module == $alias) {
                // OK, we can use a 'fake' module name here
                $path = '/' . $pubtypes[$ptid]['name'] . "/";
            } else {
                $path = '/' . $module . '/' . $pubtypes[$ptid]['name'] . "/";
            }

            // Check to see if we want to encode using Title
            if( $encodeUsingTitle )
            {
                $path .= articles_encodeUsingTitle($id, $encodeUsingTitle, $ptid);
            } else {
                $path .= $id;
            }

        } else {

            $path = '/' . $module . "/";
            // Check to see if we want to encode using Title
            if( $encodeUsingTitle )
            {
                $path .= articles_encodeUsingTitle($id, $encodeUsingTitle, '');
            } else {
                $path .= "$id";
            }
        }
        // TODO: do we want to include categories in the display URL too someday ?
    } elseif ($func == 'redirect' && isset($id)) {
        if (isset($ptid) && isset($pubtypes[$ptid])) {
            $alias = xarModGetAlias($pubtypes[$ptid]['name']);
            if ($module == $alias) {
                // OK, we can use a 'fake' module name here
                $path = '/' . $pubtypes[$ptid]['name'] . "/redirect/$id";
            } else {
                $path = '/' . $module . '/' . $pubtypes[$ptid]['name'] . "/redirect/$id";
            }
        } else {
            $path = '/' . $module . "/redirect/$id";
        }
    } elseif ($func == 'archive') {
        if (isset($ptid) && isset($pubtypes[$ptid])) {
            $alias = xarModGetAlias($pubtypes[$ptid]['name']);
            if ($module == $alias) {
                // OK, we can use a 'fake' module name here
                $module = $pubtypes[$ptid]['name'];
            } else {
                $module .= '/' . $pubtypes[$ptid]['name'];
            }
        }
        if (!empty($month)) {
            if ($month == 'all') {
                $path = '/' . $module . '/archive/' . $month .'/';
            } else {
                list($year,$mon) = split('-',$month);
                $path = '/' . $module . '/archive/' . $year .'/'. $mon . '/';
            }
        } else {
            $path = '/' . $module . '/archive/';
        }
    } elseif ($func == 'viewmap') {
        if (isset($ptid) && isset($pubtypes[$ptid])) {
            $alias = xarModGetAlias($pubtypes[$ptid]['name']);
            if ($module == $alias) {
                // OK, we can use a 'fake' module name here
                $module = $pubtypes[$ptid]['name'];
            } else {
                $module .= '/' . $pubtypes[$ptid]['name'];
            }
        }
        $path = '/' . $module . '/map/';
    } elseif ($func == 'search') {
        if (isset($ptid) && isset($pubtypes[$ptid])) {
            $alias = xarModGetAlias($pubtypes[$ptid]['name']);
            if ($module == $alias) {
                // OK, we can use a 'fake' module name here
                $module = $pubtypes[$ptid]['name'];
            } else {
                $module .= '/' . $pubtypes[$ptid]['name'];
            }
        }
        $path = '/' . $module . '/search';
        if (!empty($catid)) {
            $path .= '/c' . $catid;
        }
    }
    // anything else does not have a short URL equivalent

// TODO: add *any* extra args we didn't use yet here
    // add some other module arguments as standard URL parameters
    if (!empty($path)) {
        // search
        if (isset($q)) {
            $path .= $join . 'q=' . $q;
            $join = '&';
        }
        // by author
        if (isset($author)) {
            $path .= $join . 'author=' . $author;
            $join = '&';
        }
        // by startdate
        if (isset($start)) {
            $path .= $join . 'start=' . $start;
            $join = '&';
        }
        // by enddate
        if (isset($end)) {
            $path .= $join . 'end=' . $end;
            $join = '&';
        }
        // by status
        if (isset($status)) {
            $path .= $join . 'status=' . $status;
            $join = '&';
        }
        // field list
        if (isset($fields)) {
            foreach ($fields as $field => $val) {
                $path .= $join . 'fields[' . $field . ']=' . $val;
                $join = '&';
            }
        }
        // search type
        if (isset($searchtype)) {
            $path .= $join . 'searchtype=' . $searchtype;
            $join = '&';
        }
        // sort
        if (isset($sort)) {
            $path .= $join . 'sort=' . $sort;
            $join = '&';
        }
        // letter
        if (isset($letter)) {
            $path .= $join . 'letter=' . $letter;
            $join = '&';
        }
        // pager
        if (isset($startnum) && $startnum != 1) {
            $path .= $join . 'startnum=' . $startnum;
            $join = '&';
        }
        // multi-page articles
        if (isset($page)) {
            $path .= $join . 'page=' . $page;
            $join = '&';
        }
        // number of columns
        if (isset($numcols)) {
            $path .= $join . 'numcols=' . $numcols;
            $join = '&';
        }
        // view map by ...
        if (isset($by)) {
            $path .= $join . 'by=' . $by;
            $join = '&';
        }
    }

    return $path;
}

function articles_encodeUsingTitle( $id, $encodeUsingTitle = 1, $ptid = '' )
{
    $searchArgs['id'] = $id;
    $article = xarModAPIFunc('articles','user','get', $searchArgs);

    if (empty($article)) {
        // default to just the article ID
        $path = $id;
        return $path;
    }

    switch ($encodeUsingTitle)
    {
        case 1:
            $dupeResolutionMethod = 'Append Date';
            break;
        case 2:
            $dupeResolutionMethod = 'Append ID';
            break;
        case 3:
            $dupeResolutionMethod = 'Use ID';
            break;
        case 4:
        default:
            $dupeResolutionMethod = 'Ignore';
            break;
    }

    if ($dupeResolutionMethod != 'Ignore') {
        $searchArgs = array();
        $searchArgs['search'] = $article['title'];
        $searchArgs['searchfields'] = array('title');
        $searchArgs['searchtype'] = 'equal whole string';
        // if $ptid is set, it will be part of the URL so we can use it to refine the search
        if (!empty($ptid)) {
            $searchArgs['ptid'] = $article['pubtypeid'];
        }

        $articles = xarModAPIFunc('articles', 'user', 'getall', $searchArgs);
    }

    if ( strpos($article['title'],'_') === FALSE )
    {
        $article['title'] = str_replace(' ','_',$article['title']);
    }

    $encodedTitle = rawurlencode($article['title']);
    // the URL encoded / (%2F) is not accepted by Apache in PATH_INFO
    $encodedTitle = str_replace('%2F','/',$encodedTitle);

    if ($dupeResolutionMethod == 'Ignore') {
        // Ignore duplicates
        $path = $encodedTitle;

    // Check to find out how many articles come back from the search.
    } elseif ( count($articles) == 1 ) {
        // Only finding one article through search, we're good to go.
        $path = $encodedTitle;

    } elseif (count($articles) == 0) {
        // Can't find article through search, won't be able to find it on decode
        // default to just the article ID
        $path = $id;

    } else {
        // Finding multiple articles through search, add a duplication resolution flag
        switch( $dupeResolutionMethod )
        {
            case 'Append ID':
                // User Title and ID
                $path = $encodedTitle .'/'.$id;
                break;

            case 'Append Date':
                // User Title and Date

                $path = $encodedTitle .'/'.date('Y-m-d H:i',$article['pubdate']) ;
                break;

            case 'Use ID':
            default:
                // Just use ID instead of title
                $path = $id;
        }
    }

    return $path;
}
?>
