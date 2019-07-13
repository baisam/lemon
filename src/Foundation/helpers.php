<?php
/**
 * helpers.php
 * BaiSam admin
 *
 * Created by realeff on 2018/11/02.
 * Copyright ©2018 Jiangxi baisam information technology co., LTD. All rights reserved.
 */

/**#@+
 * Constants for expressing human-readable data sizes in their respective number of bytes.
 *
 * @since 4.4.0
 */
define( 'KB_IN_BYTES', 1024 );
define( 'MB_IN_BYTES', 1024 * KB_IN_BYTES );
define( 'GB_IN_BYTES', 1024 * MB_IN_BYTES );
define( 'TB_IN_BYTES', 1024 * GB_IN_BYTES );
/**#@-*/

/**#@+
 * Constants for expressing human-readable intervals
 * in their respective number of seconds.
 *
 * Please note that these values are approximate and are provided for convenience.
 * For example, MONTH_IN_SECONDS wrongly assumes every month has 30 days and
 * YEAR_IN_SECONDS does not take leap years into account.
 *
 * If you need more accuracy please consider using the DateTime class (https://secure.php.net/manual/en/class.datetime.php).
 *
 * @since 3.5.0
 * @since 4.4.0 Introduced `MONTH_IN_SECONDS`.
 */
define( 'MINUTE_IN_SECONDS', 60 );
define( 'HOUR_IN_SECONDS',   60 * MINUTE_IN_SECONDS );
define( 'DAY_IN_SECONDS',    24 * HOUR_IN_SECONDS   );
define( 'WEEK_IN_SECONDS',    7 * DAY_IN_SECONDS    );
define( 'MONTH_IN_SECONDS',  30 * DAY_IN_SECONDS    );
define( 'YEAR_IN_SECONDS',  365 * DAY_IN_SECONDS    );
/**#@-*/

/**
 * Add slashes to a string or array of strings.
 *
 * This should be used when preparing data for core API that expects slashed data.
 * This should not be used to escape data going directly into an SQL query.
 *
 * @since 3.6.0
 *
 * @param string|array $value String or array of strings to slash.
 * @return string|array Slashed $value
 */
function wp_slash( $value ) {
    if ( is_array( $value ) ) {
        foreach ( $value as $k => $v ) {
            if ( is_array( $v ) ) {
                $value[$k] = wp_slash( $v );
            } else {
                $value[$k] = addslashes( $v );
            }
        }
    } else {
        $value = addslashes( $value );
    }

    return $value;
}

/**
 * Remove slashes from a string or array of strings.
 *
 * This should be used to remove slashes from data passed to core API that
 * expects data to be unslashed.
 *
 * @since 3.6.0
 *
 * @param string|array $value String or array of strings to unslash.
 * @return string|array Unslashed $value
 */
function wp_unslash( $value ) {
    return stripslashes_deep( $value );
}


/**
 * Extract and return the first URL from passed content.
 *
 * @since 3.6.0
 *
 * @param string $content A string which might contain a URL.
 * @return string|false The found URL.
 */
function get_url_in_content( $content ) {
    if ( empty( $content ) ) {
        return false;
    }

    if ( preg_match( '/<a\s[^>]*?href=([\'"])(.+?)\1/is', $content, $matches ) ) {
        return esc_url_raw( $matches[2] );
    }

    return false;
}

/**
 * Maps a function to all non-iterable elements of an array or an object.
 *
 * This is similar to `array_walk_recursive()` but acts upon objects too.
 *
 * @since 4.4.0
 *
 * @param mixed    $value    The array, object, or scalar.
 * @param callable $callback The function to map onto $value.
 * @return mixed The value with the callback applied to all non-arrays and non-objects inside it.
 */
function map_deep( $value, $callback ) {
    if ( is_array( $value ) ) {
        foreach ( $value as $index => $item ) {
            $value[ $index ] = map_deep( $item, $callback );
        }
    } elseif ( is_object( $value ) ) {
        $object_vars = get_object_vars( $value );
        foreach ( $object_vars as $property_name => $property_value ) {
            $value->$property_name = map_deep( $property_value, $callback );
        }
    } else {
        $value = call_user_func( $callback, $value );
    }

    return $value;
}
/**
 * Parses a string into variables to be stored in an array.
 *
 * Uses {@link https://secure.php.net/parse_str parse_str()} and stripslashes if
 * {@link https://secure.php.net/magic_quotes magic_quotes_gpc} is on.
 *
 * @since 2.2.1
 *
 * @param string $string The string to be parsed.
 * @param array  $array  Variables will be stored in this array.
 */
function wp_parse_str( $string, &$array ) {
    parse_str( $string, $array );
    if ( get_magic_quotes_gpc() )
        $array = stripslashes_deep( $array );
}

/**
 * Navigates through an array, object, or scalar, and removes slashes from the values.
 *
 * @since 2.0.0
 *
 * @param mixed $value The value to be stripped.
 * @return mixed Stripped value.
 */
function stripslashes_deep( $value ) {
    return map_deep( $value, 'stripslashes_from_strings_only' );
}

/**
 * Callback function for `stripslashes_deep()` which strips slashes from strings.
 *
 * @since 4.4.0
 *
 * @param mixed $value The array or string to be stripped.
 * @return mixed $value The stripped value.
 */
function stripslashes_from_strings_only( $value ) {
    return is_string( $value ) ? stripslashes( $value ) : $value;
}

/**
 * Navigates through an array, object, or scalar, and encodes the values to be used in a URL.
 *
 * @since 2.2.0
 *
 * @param mixed $value The array or string to be encoded.
 * @return mixed $value The encoded value.
 */
function urlencode_deep( $value ) {
    return map_deep( $value, 'urlencode' );
}

/**
 * Navigates through an array, object, or scalar, and raw-encodes the values to be used in a URL.
 *
 * @since 3.4.0
 *
 * @param mixed $value The array or string to be encoded.
 * @return mixed $value The encoded value.
 */
function rawurlencode_deep( $value ) {
    return map_deep( $value, 'rawurlencode' );
}

/**
 * Navigates through an array, object, or scalar, and decodes URL-encoded values
 *
 * @since 4.4.0
 *
 * @param mixed $value The array or string to be decoded.
 * @return mixed $value The decoded value.
 */
function urldecode_deep( $value ) {
    return map_deep( $value, 'urldecode' );
}

/**
 * Add leading zeros when necessary.
 *
 * If you set the threshold to '4' and the number is '10', then you will get
 * back '0010'. If you set the threshold to '4' and the number is '5000', then you
 * will get back '5000'.
 *
 * Uses sprintf to append the amount of zeros based on the $threshold parameter
 * and the size of the number. If the number is large enough, then no zeros will
 * be appended.
 *
 * @since 0.71
 *
 * @param int $number     Number to append zeros to if not greater than threshold.
 * @param int $threshold  Digit places number needs to be to not have zeros added.
 * @return string Adds leading zeros to number if needed.
 */
function zeroise( $number, $threshold ) {
    return sprintf( '%0' . $threshold . 's', $number );
}

/**
 * Normalize EOL characters and strip duplicate whitespace.
 *
 * @since 2.7.0
 *
 * @param string $str The string to normalize.
 * @return string The normalized string.
 */
function normalize_whitespace( $str ) {
    $str  = trim( $str );
    $str  = str_replace( "\r", "\n", $str );
    $str  = preg_replace( array( '/\n+/', '/[ \t]+/' ), array( "\n", ' ' ), $str );
    return $str;
}

/**
 * Properly strip all HTML tags including script and style
 *
 * This differs from strip_tags() because it removes the contents of
 * the `<script>` and `<style>` tags. E.g. `strip_tags( '<script>something</script>' )`
 * will return 'something'. wp_strip_all_tags will return ''
 *
 * @since 2.9.0
 *
 * @param string $string        String containing HTML tags
 * @param bool   $remove_breaks Optional. Whether to remove left over line breaks and white space chars
 * @return string The processed string.
 */
function wp_strip_all_tags($string, $remove_breaks = false) {
    $string = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $string );
    $string = strip_tags($string);

    if ( $remove_breaks )
        $string = preg_replace('/[\r\n\t ]+/', ' ', $string);

    return trim( $string );
}

/**
 * Safely extracts not more than the first $count characters from html string.
 *
 * UTF-8, tags and entities safe prefix extraction. Entities inside will *NOT*
 * be counted as one character. For example &amp; will be counted as 4, &lt; as
 * 3, etc.
 *
 * @since 2.5.0
 *
 * @param string $str   String to get the excerpt from.
 * @param int    $count Maximum number of characters to take.
 * @param string $more  Optional. What to append if $str needs to be trimmed. Defaults to empty string.
 * @return string The excerpt.
 */
function wp_html_excerpt( $str, $count, $more = null ) {
    if ( null === $more )
        $more = '';
    $str = wp_strip_all_tags( $str, true );
    $excerpt = mb_substr( $str, 0, $count );
    // remove part of an entity at the end
    $excerpt = preg_replace( '/&[^;\s]{0,6}$/', '', $excerpt );
    if ( $str != $excerpt )
        $excerpt = trim( $excerpt ) . $more;
    return $excerpt;
}

/**
 * Build URL query based on an associative and, or indexed array.
 *
 * This is a convenient function for easily building url queries. It sets the
 * separator to '&' and uses _http_build_query() function.
 *
 * @since 2.3.0
 *
 * @see _http_build_query() Used to build the query
 * @link https://secure.php.net/manual/en/function.http-build-query.php for more on what
 *		 http_build_query() does.
 *
 * @param array $data URL-encode key/value pairs.
 * @return string URL-encoded string.
 */
function build_query( $data ) {
    return _http_build_query( $data, null, '&', '', false );
}

/**
 * From php.net (modified by Mark Jaquith to behave like the native PHP5 function).
 *
 * @since 3.2.0
 * @access private
 *
 * @see https://secure.php.net/manual/en/function.http-build-query.php
 *
 * @param array|object  $data       An array or object of data. Converted to array.
 * @param string        $prefix     Optional. Numeric index. If set, start parameter numbering with it.
 *                                  Default null.
 * @param string        $sep        Optional. Argument separator; defaults to 'arg_separator.output'.
 *                                  Default null.
 * @param string        $key        Optional. Used to prefix key name. Default empty.
 * @param bool          $urlencode  Optional. Whether to use urlencode() in the result. Default true.
 *
 * @return string The query string.
 */
function _http_build_query( $data, $prefix = null, $sep = null, $key = '', $urlencode = true ) {
    $ret = array();

    foreach ( (array) $data as $k => $v ) {
        if ( $urlencode)
            $k = urlencode($k);
        if ( is_int($k) && $prefix != null )
            $k = $prefix.$k;
        if ( !empty($key) )
            $k = $key . '%5B' . $k . '%5D';
        if ( $v === null )
            continue;
        elseif ( $v === false )
            $v = '0';

        if ( is_array($v) || is_object($v) )
            array_push($ret,_http_build_query($v, '', $sep, $k, $urlencode));
        elseif ( $urlencode )
            array_push($ret, $k.'='.urlencode($v));
        else
            array_push($ret, $k.'='.$v);
    }

    if ( null === $sep )
        $sep = ini_get('arg_separator.output');

    return implode($sep, $ret);
}

/**
 * Retrieves a modified URL query string.
 *
 * You can rebuild the URL and append query variables to the URL query by using this function.
 * There are two ways to use this function; either a single key and value, or an associative array.
 *
 * Using a single key and value:
 *
 *     add_query_arg( 'key', 'value', 'http://example.com' );
 *
 * Using an associative array:
 *
 *     add_query_arg( array(
 *         'key1' => 'value1',
 *         'key2' => 'value2',
 *     ), 'http://example.com' );
 *
 * Omitting the URL from either use results in the current URL being used
 * (the value of `$_SERVER['REQUEST_URI']`).
 *
 * Values are expected to be encoded appropriately with urlencode() or rawurlencode().
 *
 * Setting any query variable's value to boolean false removes the key (see remove_query_arg()).
 *
 * Important: The return value of add_query_arg() is not escaped by default. Output should be
 * late-escaped with esc_url() or similar to help prevent vulnerability to cross-site scripting
 * (XSS) attacks.
 *
 * @since 1.5.0
 *
 * @param string|array $key   Either a query variable key, or an associative array of query variables.
 * @param string       $value Optional. Either a query variable value, or a URL to act upon.
 * @param string       $url   Optional. A URL to act upon.
 * @return string New URL query string (unescaped).
 */
function add_query_arg() {
    $args = func_get_args();
    if ( is_array( $args[0] ) ) {
        if ( count( $args ) < 2 || false === $args[1] )
            $uri = $_SERVER['REQUEST_URI'];
        else
            $uri = $args[1];
    } else {
        if ( count( $args ) < 3 || false === $args[2] )
            $uri = $_SERVER['REQUEST_URI'];
        else
            $uri = $args[2];
    }

    if ( $frag = strstr( $uri, '#' ) )
        $uri = substr( $uri, 0, -strlen( $frag ) );
    else
        $frag = '';

    if ( 0 === stripos( $uri, 'http://' ) ) {
        $protocol = 'http://';
        $uri = substr( $uri, 7 );
    } elseif ( 0 === stripos( $uri, 'https://' ) ) {
        $protocol = 'https://';
        $uri = substr( $uri, 8 );
    } else {
        $protocol = '';
    }

    if ( strpos( $uri, '?' ) !== false ) {
        list( $base, $query ) = explode( '?', $uri, 2 );
        $base .= '?';
    } elseif ( $protocol || strpos( $uri, '=' ) === false ) {
        $base = $uri . '?';
        $query = '';
    } else {
        $base = '';
        $query = $uri;
    }

    wp_parse_str( $query, $qs );
    $qs = urlencode_deep( $qs ); // this re-URL-encodes things that were already in the query string
    if ( is_array( $args[0] ) ) {
        foreach ( $args[0] as $k => $v ) {
            $qs[ $k ] = $v;
        }
    } else {
        $qs[ $args[0] ] = $args[1];
    }

    foreach ( $qs as $k => $v ) {
        if ( $v === false )
            unset( $qs[$k] );
    }

    $ret = build_query( $qs );
    $ret = trim( $ret, '?' );
    $ret = preg_replace( '#=(&|$)#', '$1', $ret );
    $ret = $protocol . $base . $ret . $frag;
    $ret = rtrim( $ret, '?' );
    return $ret;
}

/**
 * Removes an item or items from a query string.
 *
 * @since 1.5.0
 *
 * @param string|array $key   Query key or keys to remove.
 * @param bool|string  $query Optional. When false uses the current URL. Default false.
 * @return string New URL query string.
 */
function remove_query_arg( $key, $query = false ) {
    if ( is_array( $key ) ) { // removing multiple keys
        foreach ( $key as $k )
            $query = add_query_arg( $k, false, $query );
        return $query;
    }
    return add_query_arg( $key, false, $query );
}

/**
 * Generate a random UUID (version 4).
 *
 * @since 4.7.0
 *
 * @return string UUID.
 */
function wp_generate_uuid4() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
        mt_rand( 0, 0xffff ),
        mt_rand( 0, 0x0fff ) | 0x4000,
        mt_rand( 0, 0x3fff ) | 0x8000,
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}

/**
 * Validates that a UUID is valid.
 *
 * @since 4.9.0
 *
 * @param mixed $uuid    UUID to check.
 * @param int   $version Specify which version of UUID to check against. Default is none, to accept any UUID version. Otherwise, only version allowed is `4`.
 * @return bool The string is a valid UUID or false on failure.
 */
function wp_is_uuid( $uuid, $version = null ) {

    if ( ! is_string( $uuid ) ) {
        return false;
    }

    if ( is_numeric( $version ) ) {
        if ( 4 !== (int) $version ) {
            _doing_it_wrong( __FUNCTION__, __( 'Only UUID V4 is supported at this time.' ), '4.9.0' );
            return false;
        }
        $regex = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/';
    } else {
        $regex = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/';
    }

    return (bool) preg_match( $regex, $uuid );
}

/**
 * Converts a shorthand byte value to an integer byte value.
 *
 * @since 2.3.0
 * @since 4.6.0 Moved from media.php to load.php.
 *
 * @link https://secure.php.net/manual/en/function.ini-get.php
 * @link https://secure.php.net/manual/en/faq.using.php#faq.using.shorthandbytes
 *
 * @param string $value A (PHP ini) byte value, either shorthand or ordinary.
 * @return int An integer byte value.
 */
function wp_convert_hr_to_bytes( $value ) {
    $value = strtolower( trim( $value ) );
    $bytes = (int) $value;

    if ( false !== strpos( $value, 'g' ) ) {
        $bytes *= GB_IN_BYTES;
    } elseif ( false !== strpos( $value, 'm' ) ) {
        $bytes *= MB_IN_BYTES;
    } elseif ( false !== strpos( $value, 'k' ) ) {
        $bytes *= KB_IN_BYTES;
    }

    // Deal with large (float) values which run into the maximum integer size.
    return min( $bytes, PHP_INT_MAX );
}

/**
 * 生成自动编号
 * @example
 *   generate_serial_number('20130700001T', 5, '201307', 'T');
 *
 * @param string $sn 输入序号
 * @param int $length 编号长度
 * @param string $prefix 编号前缀
 * @param string $suffix 编号后缀
 * @param int $addend 增加值
 * @return @string
 */
function generate_serial_number($sn, $length, $prefix = '', $suffix = '', $addend = 1) {
    if (empty($sn)) {
        return $prefix . str_pad('1', $length, '0', STR_PAD_LEFT) . $suffix;
    }

    $sn_prefix = substr($sn, 0, strlen($prefix));
    $sn_id     = strlen($suffix) > 0 ? substr($sn, strlen($prefix), strlen($suffix)*-1) : substr($sn, strlen($prefix));

    if ($sn_prefix === $prefix && $sn_id) {
        for ($i=strlen($sn_id)-1; $addend>0; $i--) {
            // 48-57,  65-90,  97-122
            if ($i < 0) {
                // 补位
                $sn_id = '0'. $sn_id;
                $i = 0;
            }

            $code = ord($sn_id[$i]);
            if ($code < 48 || ($code > 57 && $code < 65) ||
                ($code > 90 && $code < 97) || $code > 122) {
                continue;
            }

            if ($code < 65) {
                // 数字0-9
                $sn_id[$i] = chr(48 + ($addend + $code - 48) % 10);
                $addend = intval(($addend + $code - 48) / 10);
            }
            else if ($code > 90) {
                // 字母a-z
                $sn_id[$i] = chr(97 + ($addend + $code - 97) % 26);
                $addend = intval(($addend + $code - 97) / 26);
            }
            else {
                // 字母A-Z
                $sn_id[$i] = chr(65 + ($addend + $code - 65) % 26);
                $addend = intval(($addend + $code - 65) / 26);
            }
        }
    }
    else {
        $sn_id = $addend;
    }
    $sn_id = str_pad($sn_id, $length, '0', STR_PAD_LEFT);

    return $prefix .$sn_id .$suffix;
}

/**
 * 转换字符内容为数组内容
 */
function parse_string_info($string) {
    $info = array();

    if (empty($string)) {
        return $info;
    }

    if (preg_match_all('
    @^\s*                           # Start at the beginning of a line, ignoring leading whitespace
    ((?:
      [^=;\[\]]|                    # Key names cannot contain equal signs, semi-colons or square brackets,
      \[[^\[\]]*\]                  # unless they are balanced and not nested
    )+?)
    \s*=\s*                         # Key value pairs are separated by equal signs (ignoring white-space)
    (?:
      ("(?:[^"]|(?<=\\\\)")*")|     # Double-quoted string, which may contain slash-escaped quotes/slashes
      (\'(?:[^\']|(?<=\\\\)\')*\')| # Single-quoted string, which may contain slash-escaped quotes/slashes
      ([^\r\n]*?)                   # Non-quoted string
    )\s*$                           # Stop at the next end of a line, ignoring trailing whitespace
    @msx', $string, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            // Fetch the key and value string
            $i = 0;
            foreach (array('key', 'value1', 'value2', 'value3') as $var) {
                $$var = isset($match[++$i]) ? $match[$i] : '';
            }
            $value = stripslashes(substr($value1, 1, -1)) . stripslashes(substr($value2, 1, -1)) . $value3;

            // Parse array syntax
            $keys = preg_split('/\]?\[/', rtrim($key, ']'));
            $last = array_pop($keys);
            $parent = &$info;

            // Create nested arrays
            foreach ($keys as $key) {
                if ($key == '') {
                    $key = count($parent);
                }
                if (!isset($parent[$key]) || !is_array($parent[$key])) {
                    $parent[$key] = array();
                }
                $parent = &$parent[$key];
            }

            // Handle PHP constants
            if (defined($value)) {
                $value = constant($value);
            }

            // Insert actual value
            if ($last == '') {
                $last = count($parent);
            }
            $parent[$last] = $value;
        }
    }

    return $info;
}


/**
 * 把返回的数据集转换成Tree
 * @access public
 * @param array $list 要转换的数据集
 * @param string $pid parent标记字段
 * @param string $child child标记字段
 * @return array
 */
function list_to_tree($list, $pk='id', $pid = 'pid', $child = '_child') {
    // 创建Tree
    $tree = array();

    // 创建基于主键的数组引用
    $refer = array();
    foreach ($list as $key => $data) {
        $refer[$data[$pk]] =& $list[$key];
        $tree[$data[$pk]] =& $list[$key];
    }
    foreach ($list as $key => $data) {
        // 判断是否存在parent
        $parentId = $data[$pid];
        if (isset($refer[$parentId])) {
            unset($tree[$data[$pk]]);

            $parent =& $refer[$parentId];
            $childs = isset($parent[$child]) ? $parent[$child] : [];
            $childs[] =& $list[$key];
            $parent[$child] = $childs;
        }
    }

    return $tree;
}

/**
 * 将Tree数据转成List数据
 * @param array $tree 要转换的Tree数据
 * @param string $pk
 * @param string $icon
 * @param string $child
 * @return array
 */
function tree_to_list($tree, $pk, $icon = ' ', $child = '_child') {
    $list = array();

    $next = array();
    $depth = 0; $_icon = ''; $__icon= '                                ';
    // icon  │├└ 对应 abc

    while ($depth > -1) {
        $data = current($tree);
        if (!empty($data)) {
            $list[] =& $data;
            if (false !== next($tree)) {
                $next[$depth] =& $tree;

                if ($icon && $depth) {
                    $__icon[$depth-1] == 'b' && $__icon[$depth-1] = 'a';
                    $__icon[$depth] = 'b';
                    $_icon = rtrim($__icon, ' ');
                }
            }
            else if ($icon && $depth) {
                $__icon[$depth-1] == 'b' && $__icon[$depth-1] = 'a';
                $__icon[$depth] = 'c';
                $_icon = rtrim($__icon, ' ');
                $__icon[$depth] = ' ';
            }

            $data['_depth'] = $depth;
            $icon && $data['_icon'] = strtr($_icon, array('a' => '│', 'b' => '├', 'c' => '└', ' ' => $icon));

            if (isset($data[$child])) {
                $depth++;
                unset($tree);
                $tree = $data[$child];
                unset($data[$child]);
                $data['_has_child'] = true;
            }
            else {
                $data['_has_child'] = false;
            }
        }
        else {
            $icon && ($__icon[$depth] = ' ') && ($_icon = '');
            $depth--;

            if (isset($next[$depth])) {
                unset($tree);
                $tree =& $next[$depth];
                unset($next[$depth]);
            }
        }

        unset($data);
    }

    return $list;
}


if (function_exists('mb_substr_replace') === false)
{
    function mb_substr_replace($string, $replacement, $start, $length = null, $encoding = null)
    {
        if (is_array($string)) {
            $arr = [];
            foreach ($string as $_key => $_str) {
                $arr[$_key] = mb_substr_replace($_str, $replacement, $start, $length, $encoding);
            }

            return $arr;
        }

        if (extension_loaded('mbstring') === true)
        {
            $string_length = (is_null($encoding) === true) ? mb_strlen($string) : mb_strlen($string, $encoding);

            if ($start < 0)
            {
                $start = max(0, $string_length + $start);
            }

            else if ($start > $string_length)
            {
                $start = $string_length;
            }

            if ($length < 0)
            {
                $length = max(0, $string_length - $start + $length);
            }

            else if ((is_null($length) === true) || ($length > $string_length))
            {
                $length = $string_length;
            }

            if (($start + $length) > $string_length)
            {
                $length = $string_length - $start;
            }

            if (is_null($encoding) === true)
            {
                return mb_substr($string, 0, $start) . $replacement . mb_substr($string, $start + $length, $string_length - $start - $length);
            }

            return mb_substr($string, 0, $start, $encoding) . $replacement . mb_substr($string, $start + $length, $string_length - $start - $length, $encoding);
        }

        return (is_null($length) === true) ? substr_replace($string, $replacement, $start) : substr_replace($string, $replacement, $start, $length);
    }
}