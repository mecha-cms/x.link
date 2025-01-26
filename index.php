<?php

namespace x\link {
    // Get sub-folder path relative to the server’s root
    $sub = \trim(\strtr(\PATH . \D, [\rtrim(\strtr($_SERVER['DOCUMENT_ROOT'], '/', \D), \D) . \D => ""]), \D);
    // Set correct base URL
    \define(__NAMESPACE__ . "\\host", $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? "");
    \define(__NAMESPACE__ . "\\r", \x\link\host . ("" !== $sub ? '/' . \strtr($sub, \D, '/') : ""));
    function content($content) {
        if (!$content || false === \strpos($content, '<')) {
            return $content;
        }
        \extract(\lot(), \EXTR_SKIP);
        $state_content = (array) ($state->x->link->content ?? []);
        $state_data = (array) ($state->x->link->data ?? []);
        $r = "";
        foreach (\apart($content, \array_keys($state_content)) as $v) {
            if (1 !== $v[1] && 2 !== $v[1]) {
                $r .= $v[0];
                continue;
            }
            $k = '/' !== $v[0][1] ? \strtok(\substr($v[0], 1, -1), " \n\r\t>") : \P;
            if (false !== \strpos($t = \substr($v[0], 0, $v[2] ?? \strlen($v[0])), 'on') && \preg_match('/\bon[^=]+=/', $t)) {
                $e = new \HTML($v[0]);
                foreach ($e[2] as $kk => $vv) {
                    if ('on' === \substr($kk, 0, 2)) {
                        $e[$kk] = \x\link\content\script($vv, $e[2]);
                    }
                }
                $v[0] = (string) $e;
            }
            if (false !== \strpos($v[0], 'style=')) {
                $e = new \HTML($v[0]);
                if (isset($e['style'])) {
                    $e['style'] = \x\link\content\style($e['style'], $e[2]);
                }
                $v[0] = (string) $e;
            }
            if ($a = $state_content[$k] ?? 0) {
                $c = \substr($v[0], $v[2] = $v[2] ?? \strlen($v[0]), -(2 + \strlen($k) + 1));
                $e = new \HTML(\substr($v[0], 0, $v[2]));
                if ($aa = $state_data[$k] ?? 0) {
                    foreach ($aa as $kk => $vv) {
                        if (!$vv || !isset($e[$kk])) {
                            continue;
                        }
                        if (\is_callable($vv)) {
                            $e[$kk] = \fire($vv, [$e[$kk], $kk, $k], $e);
                            continue;
                        }
                        $e[$kk] = \x\link\link($e[$kk]);
                    }
                }
                if (\is_callable($a)) {
                    $c = \fire($a, [$c, $e[2]], $e);
                    $r .= $e . $c . '</' . $k . '>';
                    continue;
                }
                $r .= \x\link\link($c) . '</' . $k . '>';
                continue;
            }
            if ($a = $state_data[$k] ?? 0) {
                $e = new \HTML($v[0]);
                foreach ($a as $kk => $vv) {
                    if (!$vv || !isset($e[$kk])) {
                        continue;
                    }
                    if (\is_callable($vv)) {
                        $e[$kk] = \fire($vv, [$e[$kk], $kk, $k], $e);
                        continue;
                    }
                    $e[$kk] = \x\link\link($e[$kk]);
                }
                $r .= $e;
                continue;
            }
            $r .= $v[0];
        }
        return $r;
    }
    function kick($path) {
        return \x\link\link($path ?? \lot('url')->current());
    }
    function link($path) {
        if (\is_string($path)) {
            if ($path && '#' === $path[0]) {
                // Do not resolve hash-only value!
                return $path;
            }
            return \strtr(\long($path), ['://' . \x\link\host => '://' . \x\link\r]);
        }
        return $path;
    }
    \Hook::set('content', __NAMESPACE__ . "\\content", 0);
    \Hook::set('kick', __NAMESPACE__ . "\\kick", 0);
    \Hook::set('link', __NAMESPACE__ . "\\link", 0);
}

namespace x\link\content {
    function script($content) {
        if (false === \strpos($content, '://')) {
            return $content;
        }
        $content = \preg_replace_callback('/\bhttps?:\/\/[^\s"<]+\b\/?/i', static function ($m) {
            return \x\link\link($m[0]);
        }, $content);
        return $content;
    }
    function style($content) {
        if (false !== \strpos($content, 'url(')) {
            $content = \preg_replace_callback('/\burl\(([^()]+)\)/', static function ($m) {
                if ('"' === $m[1][0] && '"' === \substr($m[1], -1)) {
                    return 'url("' . \long(\substr($m[1], 1, -1)) . '")';
                }
                if ("'" === $m[1][0] && "'" === \substr($m[1], -1)) {
                    return "url('" . \long(\substr($m[1], 1, -1)) . "')";
                }
                return 'url(' . \long($m[1]) . ')';
            }, $content);
        }
        if (false === \strpos($content, '://')) {
            return $content;
        }
        $content = \preg_replace_callback('/\bhttps?:\/\/[^\s"<]+\b\/?/i', static function ($m) {
            return \x\link\link($m[0]);
        }, $content);
        return $content;
    }
}

namespace x\link\data\img {
    function srcset($value) {
        if (!$value) {
            return $value;
        }
        $out = "";
        foreach (\preg_split('/(\s*,\s*)(?!,)/', $value, -1, \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY) as $v) {
            if (',' === \trim($v)) {
                $out .= $v;
                continue;
            }
            $out .= \x\link\link(\rtrim($v, ','));
        }
        return $out;
    }
    if (\defined("\\TEST") && 'x.link' === \TEST && \is_file($test = __DIR__ . \D . 'test.php')) {
        require $test;
    }
}