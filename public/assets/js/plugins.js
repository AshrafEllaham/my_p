!function (e, t) {
    "use strict";
    "object" == typeof module && "object" == typeof module.exports ? module.exports = e.document ? t(e, !0) : function (e) {
        if (e.document) return t(e);
        throw new Error("jQuery requires a window with a document")
    } : t(e)
}("undefined" != typeof window ? window : this, function (w, M) {
    "use strict";

    function y(e) {
        return "function" == typeof e && "number" != typeof e.nodeType && "function" != typeof e.item
    }

    function g(e) {
        return null != e && e === e.window
    }

    var t = [], H = Object.getPrototypeOf, a = t.slice, q = t.flat ? function (e) {
            return t.flat.call(e)
        } : function (e) {
            return t.concat.apply([], e)
        }, R = t.push, B = t.indexOf, F = {}, W = F.toString, z = F.hasOwnProperty, $ = z.toString, V = $.call(Object),
        m = {}, x = w.document, X = {type: !0, src: !0, nonce: !0, noModule: !0};

    function U(e, t, n) {
        var i, o, r = (n = n || x).createElement("script");
        if (r.text = e, t) for (i in X) (o = t[i] || t.getAttribute && t.getAttribute(i)) && r.setAttribute(i, o);
        n.head.appendChild(r).parentNode.removeChild(r)
    }

    function p(e) {
        return null == e ? e + "" : "object" == typeof e || "function" == typeof e ? F[W.call(e)] || "object" : typeof e
    }

    var e = "3.6.1", T = function (e, t) {
        return new T.fn.init(e, t)
    };

    function Y(e) {
        var t = !!e && "length" in e && e.length, n = p(e);
        return !y(e) && !g(e) && ("array" === n || 0 === t || "number" == typeof t && 0 < t && t - 1 in e)
    }

    T.fn = T.prototype = {
        jquery: e, constructor: T, length: 0, toArray: function () {
            return a.call(this)
        }, get: function (e) {
            return null == e ? a.call(this) : e < 0 ? this[e + this.length] : this[e]
        }, pushStack: function (e) {
            e = T.merge(this.constructor(), e);
            return e.prevObject = this, e
        }, each: function (e) {
            return T.each(this, e)
        }, map: function (n) {
            return this.pushStack(T.map(this, function (e, t) {
                return n.call(e, t, e)
            }))
        }, slice: function () {
            return this.pushStack(a.apply(this, arguments))
        }, first: function () {
            return this.eq(0)
        }, last: function () {
            return this.eq(-1)
        }, even: function () {
            return this.pushStack(T.grep(this, function (e, t) {
                return (t + 1) % 2
            }))
        }, odd: function () {
            return this.pushStack(T.grep(this, function (e, t) {
                return t % 2
            }))
        }, eq: function (e) {
            var t = this.length, e = +e + (e < 0 ? t : 0);
            return this.pushStack(0 <= e && e < t ? [this[e]] : [])
        }, end: function () {
            return this.prevObject || this.constructor()
        }, push: R, sort: t.sort, splice: t.splice
    }, T.extend = T.fn.extend = function () {
        var e, t, n, i, o, r = arguments[0] || {}, s = 1, a = arguments.length, l = !1;
        for ("boolean" == typeof r && (l = r, r = arguments[s] || {}, s++), "object" == typeof r || y(r) || (r = {}), s === a && (r = this, s--); s < a; s++) if (null != (e = arguments[s])) for (t in e) n = e[t], "__proto__" !== t && r !== n && (l && n && (T.isPlainObject(n) || (i = Array.isArray(n))) ? (o = r[t], o = i && !Array.isArray(o) ? [] : i || T.isPlainObject(o) ? o : {}, i = !1, r[t] = T.extend(l, o, n)) : void 0 !== n && (r[t] = n));
        return r
    }, T.extend({
        expando: "jQuery" + (e + Math.random()).replace(/\D/g, ""), isReady: !0, error: function (e) {
            throw new Error(e)
        }, noop: function () {
        }, isPlainObject: function (e) {
            return !(!e || "[object Object]" !== W.call(e)) && (!(e = H(e)) || "function" == typeof (e = z.call(e, "constructor") && e.constructor) && $.call(e) === V)
        }, isEmptyObject: function (e) {
            for (var t in e) return !1;
            return !0
        }, globalEval: function (e, t, n) {
            U(e, {nonce: t && t.nonce}, n)
        }, each: function (e, t) {
            var n, i = 0;
            if (Y(e)) for (n = e.length; i < n && !1 !== t.call(e[i], i, e[i]); i++) ; else for (i in e) if (!1 === t.call(e[i], i, e[i])) break;
            return e
        }, makeArray: function (e, t) {
            t = t || [];
            return null != e && (Y(Object(e)) ? T.merge(t, "string" == typeof e ? [e] : e) : R.call(t, e)), t
        }, inArray: function (e, t, n) {
            return null == t ? -1 : B.call(t, e, n)
        }, merge: function (e, t) {
            for (var n = +t.length, i = 0, o = e.length; i < n; i++) e[o++] = t[i];
            return e.length = o, e
        }, grep: function (e, t, n) {
            for (var i = [], o = 0, r = e.length, s = !n; o < r; o++) !t(e[o], o) != s && i.push(e[o]);
            return i
        }, map: function (e, t, n) {
            var i, o, r = 0, s = [];
            if (Y(e)) for (i = e.length; r < i; r++) null != (o = t(e[r], r, n)) && s.push(o); else for (r in e) null != (o = t(e[r], r, n)) && s.push(o);
            return q(s)
        }, guid: 1, support: m
    }), "function" == typeof Symbol && (T.fn[Symbol.iterator] = t[Symbol.iterator]), T.each("Boolean Number String Function Array Date RegExp Object Error Symbol".split(" "), function (e, t) {
        F["[object " + t + "]"] = t.toLowerCase()
    });

    function i(e, t, n) {
        for (var i = [], o = void 0 !== n; (e = e[t]) && 9 !== e.nodeType;) if (1 === e.nodeType) {
            if (o && T(e).is(n)) break;
            i.push(e)
        }
        return i
    }

    function Q(e, t) {
        for (var n = []; e; e = e.nextSibling) 1 === e.nodeType && e !== t && n.push(e);
        return n
    }

    var e = function (M) {
            function d(e, t) {
                return e = "0x" + e.slice(1) - 65536, t || (e < 0 ? String.fromCharCode(65536 + e) : String.fromCharCode(e >> 10 | 55296, 1023 & e | 56320))
            }

            function H(e, t) {
                return t ? "\0" === e ? "�" : e.slice(0, -1) + "\\" + e.charCodeAt(e.length - 1).toString(16) + " " : "\\" + e
            }

            function q() {
                x()
            }

            var e, f, _, r, R, h, B, F, w, l, c, x, T, n, k, p, i, o, g, C = "sizzle" + +new Date, u = M.document, A = 0,
                W = 0, z = O(), $ = O(), V = O(), m = O(), X = function (e, t) {
                    return e === t && (c = !0), 0
                }, U = {}.hasOwnProperty, t = [], Y = t.pop, Q = t.push, E = t.push, G = t.slice, y = function (e, t) {
                    for (var n = 0, i = e.length; n < i; n++) if (e[n] === t) return n;
                    return -1
                },
                K = "checked|selected|async|autofocus|autoplay|controls|defer|disabled|hidden|ismap|loop|multiple|open|readonly|required|scoped",
                s = "[\\x20\\t\\r\\n\\f]", a = "(?:\\\\[\\da-fA-F]{1,6}" + s + "?|\\\\[^\\r\\n\\f]|[\\w-]|[^\0-\\x7f])+",
                J = "\\[" + s + "*(" + a + ")(?:" + s + "*([*^$|!~]?=)" + s + "*(?:'((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\"|(" + a + "))|)" + s + "*\\]",
                Z = ":(" + a + ")(?:\\((('((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\")|((?:\\\\.|[^\\\\()[\\]]|" + J + ")*)|.*)\\)|)",
                ee = new RegExp(s + "+", "g"), v = new RegExp("^" + s + "+|((?:^|[^\\\\])(?:\\\\.)*)" + s + "+$", "g"),
                te = new RegExp("^" + s + "*," + s + "*"), ne = new RegExp("^" + s + "*([>+~]|" + s + ")" + s + "*"),
                ie = new RegExp(s + "|>"), oe = new RegExp(Z), re = new RegExp("^" + a + "$"), b = {
                    ID: new RegExp("^#(" + a + ")"),
                    CLASS: new RegExp("^\\.(" + a + ")"),
                    TAG: new RegExp("^(" + a + "|[*])"),
                    ATTR: new RegExp("^" + J),
                    PSEUDO: new RegExp("^" + Z),
                    CHILD: new RegExp("^:(only|first|last|nth|nth-last)-(child|of-type)(?:\\(" + s + "*(even|odd|(([+-]|)(\\d*)n|)" + s + "*(?:([+-]|)" + s + "*(\\d+)|))" + s + "*\\)|)", "i"),
                    bool: new RegExp("^(?:" + K + ")$", "i"),
                    needsContext: new RegExp("^" + s + "*[>+~]|:(even|odd|eq|gt|lt|nth|first|last)(?:\\(" + s + "*((?:-\\d)?\\d*)" + s + "*\\)|)(?=[^-]|$)", "i")
                }, se = /HTML$/i, ae = /^(?:input|select|textarea|button)$/i, le = /^h\d$/i, S = /^[^{]+\{\s*\[native \w/,
                ce = /^(?:#([\w-]+)|(\w+)|\.([\w-]+))$/, ue = /[+~]/,
                D = new RegExp("\\\\[\\da-fA-F]{1,6}" + s + "?|\\\\([^\\r\\n\\f])", "g"),
                de = /([\0-\x1f\x7f]|^-?\d)|^-$|[^\0-\x1f\x7f-\uFFFF\w-]/g, fe = ye(function (e) {
                    return !0 === e.disabled && "fieldset" === e.nodeName.toLowerCase()
                }, {dir: "parentNode", next: "legend"});
            try {
                E.apply(t = G.call(u.childNodes), u.childNodes), t[u.childNodes.length].nodeType
            } catch (e) {
                E = {
                    apply: t.length ? function (e, t) {
                        Q.apply(e, G.call(t))
                    } : function (e, t) {
                        for (var n = e.length, i = 0; e[n++] = t[i++];) ;
                        e.length = n - 1
                    }
                }
            }

            function L(t, e, n, i) {
                var o, r, s, a, l, c, u = e && e.ownerDocument, d = e ? e.nodeType : 9;
                if (n = n || [], "string" != typeof t || !t || 1 !== d && 9 !== d && 11 !== d) return n;
                if (!i && (x(e), e = e || T, k)) {
                    if (11 !== d && (a = ce.exec(t))) if (o = a[1]) {
                        if (9 === d) {
                            if (!(c = e.getElementById(o))) return n;
                            if (c.id === o) return n.push(c), n
                        } else if (u && (c = u.getElementById(o)) && g(e, c) && c.id === o) return n.push(c), n
                    } else {
                        if (a[2]) return E.apply(n, e.getElementsByTagName(t)), n;
                        if ((o = a[3]) && f.getElementsByClassName && e.getElementsByClassName) return E.apply(n, e.getElementsByClassName(o)), n
                    }
                    if (f.qsa && !m[t + " "] && (!p || !p.test(t)) && (1 !== d || "object" !== e.nodeName.toLowerCase())) {
                        if (c = t, u = e, 1 === d && (ie.test(t) || ne.test(t))) {
                            for ((u = ue.test(t) && me(e.parentNode) || e) === e && f.scope || ((s = e.getAttribute("id")) ? s = s.replace(de, H) : e.setAttribute("id", s = C)), r = (l = h(t)).length; r--;) l[r] = (s ? "#" + s : ":scope") + " " + P(l[r]);
                            c = l.join(",")
                        }
                        try {
                            return E.apply(n, u.querySelectorAll(c)), n
                        } catch (e) {
                            m(t, !0)
                        } finally {
                            s === C && e.removeAttribute("id")
                        }
                    }
                }
                return F(t.replace(v, "$1"), e, n, i)
            }

            function O() {
                var n = [];

                function i(e, t) {
                    return n.push(e + " ") > _.cacheLength && delete i[n.shift()], i[e + " "] = t
                }

                return i
            }

            function N(e) {
                return e[C] = !0, e
            }

            function j(e) {
                var t = T.createElement("fieldset");
                try {
                    return !!e(t)
                } catch (e) {
                    return !1
                } finally {
                    t.parentNode && t.parentNode.removeChild(t)
                }
            }

            function he(e, t) {
                for (var n = e.split("|"), i = n.length; i--;) _.attrHandle[n[i]] = t
            }

            function pe(e, t) {
                var n = t && e, i = n && 1 === e.nodeType && 1 === t.nodeType && e.sourceIndex - t.sourceIndex;
                if (i) return i;
                if (n) for (; n = n.nextSibling;) if (n === t) return -1;
                return e ? 1 : -1
            }

            function ge(t) {
                return function (e) {
                    return "form" in e ? e.parentNode && !1 === e.disabled ? "label" in e ? "label" in e.parentNode ? e.parentNode.disabled === t : e.disabled === t : e.isDisabled === t || e.isDisabled !== !t && fe(e) === t : e.disabled === t : "label" in e && e.disabled === t
                }
            }

            function I(s) {
                return N(function (r) {
                    return r = +r, N(function (e, t) {
                        for (var n, i = s([], e.length, r), o = i.length; o--;) e[n = i[o]] && (e[n] = !(t[n] = e[n]))
                    })
                })
            }

            function me(e) {
                return e && void 0 !== e.getElementsByTagName && e
            }

            for (e in f = L.support = {}, R = L.isXML = function (e) {
                var t = e && e.namespaceURI, e = e && (e.ownerDocument || e).documentElement;
                return !se.test(t || e && e.nodeName || "HTML")
            }, x = L.setDocument = function (e) {
                var e = e ? e.ownerDocument || e : u;
                return e != T && 9 === e.nodeType && e.documentElement && (n = (T = e).documentElement, k = !R(T), u != T && (e = T.defaultView) && e.top !== e && (e.addEventListener ? e.addEventListener("unload", q, !1) : e.attachEvent && e.attachEvent("onunload", q)), f.scope = j(function (e) {
                    return n.appendChild(e).appendChild(T.createElement("div")), void 0 !== e.querySelectorAll && !e.querySelectorAll(":scope fieldset div").length
                }), f.attributes = j(function (e) {
                    return e.className = "i", !e.getAttribute("className")
                }), f.getElementsByTagName = j(function (e) {
                    return e.appendChild(T.createComment("")), !e.getElementsByTagName("*").length
                }), f.getElementsByClassName = S.test(T.getElementsByClassName), f.getById = j(function (e) {
                    return n.appendChild(e).id = C, !T.getElementsByName || !T.getElementsByName(C).length
                }), f.getById ? (_.filter.ID = function (e) {
                    var t = e.replace(D, d);
                    return function (e) {
                        return e.getAttribute("id") === t
                    }
                }, _.find.ID = function (e, t) {
                    if (void 0 !== t.getElementById && k) return (t = t.getElementById(e)) ? [t] : []
                }) : (_.filter.ID = function (e) {
                    var t = e.replace(D, d);
                    return function (e) {
                        e = void 0 !== e.getAttributeNode && e.getAttributeNode("id");
                        return e && e.value === t
                    }
                }, _.find.ID = function (e, t) {
                    if (void 0 !== t.getElementById && k) {
                        var n, i, o, r = t.getElementById(e);
                        if (r) {
                            if ((n = r.getAttributeNode("id")) && n.value === e) return [r];
                            for (o = t.getElementsByName(e), i = 0; r = o[i++];) if ((n = r.getAttributeNode("id")) && n.value === e) return [r]
                        }
                        return []
                    }
                }), _.find.TAG = f.getElementsByTagName ? function (e, t) {
                    return void 0 !== t.getElementsByTagName ? t.getElementsByTagName(e) : f.qsa ? t.querySelectorAll(e) : void 0
                } : function (e, t) {
                    var n, i = [], o = 0, r = t.getElementsByTagName(e);
                    if ("*" !== e) return r;
                    for (; n = r[o++];) 1 === n.nodeType && i.push(n);
                    return i
                }, _.find.CLASS = f.getElementsByClassName && function (e, t) {
                    if (void 0 !== t.getElementsByClassName && k) return t.getElementsByClassName(e)
                }, i = [], p = [], (f.qsa = S.test(T.querySelectorAll)) && (j(function (e) {
                    var t;
                    n.appendChild(e).innerHTML = "<a id='" + C + "'></a><select id='" + C + "-\r\\' msallowcapture=''><option selected=''></option></select>", e.querySelectorAll("[msallowcapture^='']").length && p.push("[*^$]=" + s + "*(?:''|\"\")"), e.querySelectorAll("[selected]").length || p.push("\\[" + s + "*(?:value|" + K + ")"), e.querySelectorAll("[id~=" + C + "-]").length || p.push("~="), (t = T.createElement("input")).setAttribute("name", ""), e.appendChild(t), e.querySelectorAll("[name='']").length || p.push("\\[" + s + "*name" + s + "*=" + s + "*(?:''|\"\")"), e.querySelectorAll(":checked").length || p.push(":checked"), e.querySelectorAll("a#" + C + "+*").length || p.push(".#.+[+~]"), e.querySelectorAll("\\\f"), p.push("[\\r\\n\\f]")
                }), j(function (e) {
                    e.innerHTML = "<a href='' disabled='disabled'></a><select disabled='disabled'><option/></select>";
                    var t = T.createElement("input");
                    t.setAttribute("type", "hidden"), e.appendChild(t).setAttribute("name", "D"), e.querySelectorAll("[name=d]").length && p.push("name" + s + "*[*^$|!~]?="), 2 !== e.querySelectorAll(":enabled").length && p.push(":enabled", ":disabled"), n.appendChild(e).disabled = !0, 2 !== e.querySelectorAll(":disabled").length && p.push(":enabled", ":disabled"), e.querySelectorAll("*,:x"), p.push(",.*:")
                })), (f.matchesSelector = S.test(o = n.matches || n.webkitMatchesSelector || n.mozMatchesSelector || n.oMatchesSelector || n.msMatchesSelector)) && j(function (e) {
                    f.disconnectedMatch = o.call(e, "*"), o.call(e, "[s!='']:x"), i.push("!=", Z)
                }), p = p.length && new RegExp(p.join("|")), i = i.length && new RegExp(i.join("|")), e = S.test(n.compareDocumentPosition), g = e || S.test(n.contains) ? function (e, t) {
                    var n = 9 === e.nodeType ? e.documentElement : e, t = t && t.parentNode;
                    return e === t || !(!t || 1 !== t.nodeType || !(n.contains ? n.contains(t) : e.compareDocumentPosition && 16 & e.compareDocumentPosition(t)))
                } : function (e, t) {
                    if (t) for (; t = t.parentNode;) if (t === e) return !0;
                    return !1
                }, X = e ? function (e, t) {
                    if (e === t) return c = !0, 0;
                    var n = !e.compareDocumentPosition - !t.compareDocumentPosition;
                    return n || (1 & (n = (e.ownerDocument || e) == (t.ownerDocument || t) ? e.compareDocumentPosition(t) : 1) || !f.sortDetached && t.compareDocumentPosition(e) === n ? e == T || e.ownerDocument == u && g(u, e) ? -1 : t == T || t.ownerDocument == u && g(u, t) ? 1 : l ? y(l, e) - y(l, t) : 0 : 4 & n ? -1 : 1)
                } : function (e, t) {
                    if (e === t) return c = !0, 0;
                    var n, i = 0, o = e.parentNode, r = t.parentNode, s = [e], a = [t];
                    if (!o || !r) return e == T ? -1 : t == T ? 1 : o ? -1 : r ? 1 : l ? y(l, e) - y(l, t) : 0;
                    if (o === r) return pe(e, t);
                    for (n = e; n = n.parentNode;) s.unshift(n);
                    for (n = t; n = n.parentNode;) a.unshift(n);
                    for (; s[i] === a[i];) i++;
                    return i ? pe(s[i], a[i]) : s[i] == u ? -1 : a[i] == u ? 1 : 0
                }), T
            }, L.matches = function (e, t) {
                return L(e, null, null, t)
            }, L.matchesSelector = function (e, t) {
                if (x(e), f.matchesSelector && k && !m[t + " "] && (!i || !i.test(t)) && (!p || !p.test(t))) try {
                    var n = o.call(e, t);
                    if (n || f.disconnectedMatch || e.document && 11 !== e.document.nodeType) return n
                } catch (e) {
                    m(t, !0)
                }
                return 0 < L(t, T, null, [e]).length
            }, L.contains = function (e, t) {
                return (e.ownerDocument || e) != T && x(e), g(e, t)
            }, L.attr = function (e, t) {
                (e.ownerDocument || e) != T && x(e);
                var n = _.attrHandle[t.toLowerCase()],
                    n = n && U.call(_.attrHandle, t.toLowerCase()) ? n(e, t, !k) : void 0;
                return void 0 !== n ? n : f.attributes || !k ? e.getAttribute(t) : (n = e.getAttributeNode(t)) && n.specified ? n.value : null
            }, L.escape = function (e) {
                return (e + "").replace(de, H)
            }, L.error = function (e) {
                throw new Error("Syntax error, unrecognized expression: " + e)
            }, L.uniqueSort = function (e) {
                var t, n = [], i = 0, o = 0;
                if (c = !f.detectDuplicates, l = !f.sortStable && e.slice(0), e.sort(X), c) {
                    for (; t = e[o++];) t === e[o] && (i = n.push(o));
                    for (; i--;) e.splice(n[i], 1)
                }
                return l = null, e
            }, r = L.getText = function (e) {
                var t, n = "", i = 0, o = e.nodeType;
                if (o) {
                    if (1 === o || 9 === o || 11 === o) {
                        if ("string" == typeof e.textContent) return e.textContent;
                        for (e = e.firstChild; e; e = e.nextSibling) n += r(e)
                    } else if (3 === o || 4 === o) return e.nodeValue
                } else for (; t = e[i++];) n += r(t);
                return n
            }, (_ = L.selectors = {
                cacheLength: 50,
                createPseudo: N,
                match: b,
                attrHandle: {},
                find: {},
                relative: {
                    ">": {dir: "parentNode", first: !0},
                    " ": {dir: "parentNode"},
                    "+": {dir: "previousSibling", first: !0},
                    "~": {dir: "previousSibling"}
                },
                preFilter: {
                    ATTR: function (e) {
                        return e[1] = e[1].replace(D, d), e[3] = (e[3] || e[4] || e[5] || "").replace(D, d), "~=" === e[2] && (e[3] = " " + e[3] + " "), e.slice(0, 4)
                    }, CHILD: function (e) {
                        return e[1] = e[1].toLowerCase(), "nth" === e[1].slice(0, 3) ? (e[3] || L.error(e[0]), e[4] = +(e[4] ? e[5] + (e[6] || 1) : 2 * ("even" === e[3] || "odd" === e[3])), e[5] = +(e[7] + e[8] || "odd" === e[3])) : e[3] && L.error(e[0]), e
                    }, PSEUDO: function (e) {
                        var t, n = !e[6] && e[2];
                        return b.CHILD.test(e[0]) ? null : (e[3] ? e[2] = e[4] || e[5] || "" : n && oe.test(n) && (t = h(n, !0)) && (t = n.indexOf(")", n.length - t) - n.length) && (e[0] = e[0].slice(0, t), e[2] = n.slice(0, t)), e.slice(0, 3))
                    }
                },
                filter: {
                    TAG: function (e) {
                        var t = e.replace(D, d).toLowerCase();
                        return "*" === e ? function () {
                            return !0
                        } : function (e) {
                            return e.nodeName && e.nodeName.toLowerCase() === t
                        }
                    }, CLASS: function (e) {
                        var t = z[e + " "];
                        return t || (t = new RegExp("(^|" + s + ")" + e + "(" + s + "|$)")) && z(e, function (e) {
                            return t.test("string" == typeof e.className && e.className || void 0 !== e.getAttribute && e.getAttribute("class") || "")
                        })
                    }, ATTR: function (t, n, i) {
                        return function (e) {
                            e = L.attr(e, t);
                            return null == e ? "!=" === n : !n || (e += "", "=" === n ? e === i : "!=" === n ? e !== i : "^=" === n ? i && 0 === e.indexOf(i) : "*=" === n ? i && -1 < e.indexOf(i) : "$=" === n ? i && e.slice(-i.length) === i : "~=" === n ? -1 < (" " + e.replace(ee, " ") + " ").indexOf(i) : "|=" === n && (e === i || e.slice(0, i.length + 1) === i + "-"))
                        }
                    }, CHILD: function (p, e, t, g, m) {
                        var v = "nth" !== p.slice(0, 3), y = "last" !== p.slice(-4), b = "of-type" === e;
                        return 1 === g && 0 === m ? function (e) {
                            return !!e.parentNode
                        } : function (e, t, n) {
                            var i, o, r, s, a, l, c = v != y ? "nextSibling" : "previousSibling", u = e.parentNode,
                                d = b && e.nodeName.toLowerCase(), f = !n && !b, h = !1;
                            if (u) {
                                if (v) {
                                    for (; c;) {
                                        for (s = e; s = s[c];) if (b ? s.nodeName.toLowerCase() === d : 1 === s.nodeType) return !1;
                                        l = c = "only" === p && !l && "nextSibling"
                                    }
                                    return !0
                                }
                                if (l = [y ? u.firstChild : u.lastChild], y && f) {
                                    for (h = (a = (i = (o = (r = (s = u)[C] || (s[C] = {}))[s.uniqueID] || (r[s.uniqueID] = {}))[p] || [])[0] === A && i[1]) && i[2], s = a && u.childNodes[a]; s = ++a && s && s[c] || (h = a = 0) || l.pop();) if (1 === s.nodeType && ++h && s === e) {
                                        o[p] = [A, a, h];
                                        break
                                    }
                                } else if (!1 === (h = f ? a = (i = (o = (r = (s = e)[C] || (s[C] = {}))[s.uniqueID] || (r[s.uniqueID] = {}))[p] || [])[0] === A && i[1] : h)) for (; (s = ++a && s && s[c] || (h = a = 0) || l.pop()) && ((b ? s.nodeName.toLowerCase() !== d : 1 !== s.nodeType) || !++h || (f && ((o = (r = s[C] || (s[C] = {}))[s.uniqueID] || (r[s.uniqueID] = {}))[p] = [A, h]), s !== e));) ;
                                return (h -= m) === g || h % g == 0 && 0 <= h / g
                            }
                        }
                    }, PSEUDO: function (e, r) {
                        var t, s = _.pseudos[e] || _.setFilters[e.toLowerCase()] || L.error("unsupported pseudo: " + e);
                        return s[C] ? s(r) : 1 < s.length ? (t = [e, e, "", r], _.setFilters.hasOwnProperty(e.toLowerCase()) ? N(function (e, t) {
                            for (var n, i = s(e, r), o = i.length; o--;) e[n = y(e, i[o])] = !(t[n] = i[o])
                        }) : function (e) {
                            return s(e, 0, t)
                        }) : s
                    }
                },
                pseudos: {
                    not: N(function (e) {
                        var i = [], o = [], a = B(e.replace(v, "$1"));
                        return a[C] ? N(function (e, t, n, i) {
                            for (var o, r = a(e, null, i, []), s = e.length; s--;) (o = r[s]) && (e[s] = !(t[s] = o))
                        }) : function (e, t, n) {
                            return i[0] = e, a(i, null, n, o), i[0] = null, !o.pop()
                        }
                    }), has: N(function (t) {
                        return function (e) {
                            return 0 < L(t, e).length
                        }
                    }), contains: N(function (t) {
                        return t = t.replace(D, d), function (e) {
                            return -1 < (e.textContent || r(e)).indexOf(t)
                        }
                    }), lang: N(function (n) {
                        return re.test(n || "") || L.error("unsupported lang: " + n), n = n.replace(D, d).toLowerCase(), function (e) {
                            var t;
                            do {
                                if (t = k ? e.lang : e.getAttribute("xml:lang") || e.getAttribute("lang")) return (t = t.toLowerCase()) === n || 0 === t.indexOf(n + "-")
                            } while ((e = e.parentNode) && 1 === e.nodeType);
                            return !1
                        }
                    }), target: function (e) {
                        var t = M.location && M.location.hash;
                        return t && t.slice(1) === e.id
                    }, root: function (e) {
                        return e === n
                    }, focus: function (e) {
                        return e === T.activeElement && (!T.hasFocus || T.hasFocus()) && !!(e.type || e.href || ~e.tabIndex)
                    }, enabled: ge(!1), disabled: ge(!0), checked: function (e) {
                        var t = e.nodeName.toLowerCase();
                        return "input" === t && !!e.checked || "option" === t && !!e.selected
                    }, selected: function (e) {
                        return e.parentNode && e.parentNode.selectedIndex, !0 === e.selected
                    }, empty: function (e) {
                        for (e = e.firstChild; e; e = e.nextSibling) if (e.nodeType < 6) return !1;
                        return !0
                    }, parent: function (e) {
                        return !_.pseudos.empty(e)
                    }, header: function (e) {
                        return le.test(e.nodeName)
                    }, input: function (e) {
                        return ae.test(e.nodeName)
                    }, button: function (e) {
                        var t = e.nodeName.toLowerCase();
                        return "input" === t && "button" === e.type || "button" === t
                    }, text: function (e) {
                        return "input" === e.nodeName.toLowerCase() && "text" === e.type && (null == (e = e.getAttribute("type")) || "text" === e.toLowerCase())
                    }, first: I(function () {
                        return [0]
                    }), last: I(function (e, t) {
                        return [t - 1]
                    }), eq: I(function (e, t, n) {
                        return [n < 0 ? n + t : n]
                    }), even: I(function (e, t) {
                        for (var n = 0; n < t; n += 2) e.push(n);
                        return e
                    }), odd: I(function (e, t) {
                        for (var n = 1; n < t; n += 2) e.push(n);
                        return e
                    }), lt: I(function (e, t, n) {
                        for (var i = n < 0 ? n + t : t < n ? t : n; 0 <= --i;) e.push(i);
                        return e
                    }), gt: I(function (e, t, n) {
                        for (var i = n < 0 ? n + t : n; ++i < t;) e.push(i);
                        return e
                    })
                }
            }).pseudos.nth = _.pseudos.eq, {
                radio: !0,
                checkbox: !0,
                file: !0,
                password: !0,
                image: !0
            }) _.pseudos[e] = function (t) {
                return function (e) {
                    return "input" === e.nodeName.toLowerCase() && e.type === t
                }
            }(e);
            for (e in {submit: !0, reset: !0}) _.pseudos[e] = function (n) {
                return function (e) {
                    var t = e.nodeName.toLowerCase();
                    return ("input" === t || "button" === t) && e.type === n
                }
            }(e);

            function ve() {
            }

            function P(e) {
                for (var t = 0, n = e.length, i = ""; t < n; t++) i += e[t].value;
                return i
            }

            function ye(s, e, t) {
                var a = e.dir, l = e.next, c = l || a, u = t && "parentNode" === c, d = W++;
                return e.first ? function (e, t, n) {
                    for (; e = e[a];) if (1 === e.nodeType || u) return s(e, t, n);
                    return !1
                } : function (e, t, n) {
                    var i, o, r = [A, d];
                    if (n) {
                        for (; e = e[a];) if ((1 === e.nodeType || u) && s(e, t, n)) return !0
                    } else for (; e = e[a];) if (1 === e.nodeType || u) if (o = (o = e[C] || (e[C] = {}))[e.uniqueID] || (o[e.uniqueID] = {}), l && l === e.nodeName.toLowerCase()) e = e[a] || e; else {
                        if ((i = o[c]) && i[0] === A && i[1] === d) return r[2] = i[2];
                        if ((o[c] = r)[2] = s(e, t, n)) return !0
                    }
                    return !1
                }
            }

            function be(o) {
                return 1 < o.length ? function (e, t, n) {
                    for (var i = o.length; i--;) if (!o[i](e, t, n)) return !1;
                    return !0
                } : o[0]
            }

            function _e(e, t, n, i, o) {
                for (var r, s = [], a = 0, l = e.length, c = null != t; a < l; a++) !(r = e[a]) || n && !n(r, i, o) || (s.push(r), c && t.push(a));
                return s
            }

            function we(h, p, g, m, v, e) {
                return m && !m[C] && (m = we(m)), v && !v[C] && (v = we(v, e)), N(function (e, t, n, i) {
                    var o, r, s, a = [], l = [], c = t.length, u = e || function (e, t, n) {
                            for (var i = 0, o = t.length; i < o; i++) L(e, t[i], n);
                            return n
                        }(p || "*", n.nodeType ? [n] : n, []), d = !h || !e && p ? u : _e(u, a, h, n, i),
                        f = g ? v || (e ? h : c || m) ? [] : t : d;
                    if (g && g(d, f, n, i), m) for (o = _e(f, l), m(o, [], n, i), r = o.length; r--;) (s = o[r]) && (f[l[r]] = !(d[l[r]] = s));
                    if (e) {
                        if (v || h) {
                            if (v) {
                                for (o = [], r = f.length; r--;) (s = f[r]) && o.push(d[r] = s);
                                v(null, f = [], o, i)
                            }
                            for (r = f.length; r--;) (s = f[r]) && -1 < (o = v ? y(e, s) : a[r]) && (e[o] = !(t[o] = s))
                        }
                    } else f = _e(f === t ? f.splice(c, f.length) : f), v ? v(null, t, f, i) : E.apply(t, f)
                })
            }

            function xe(m, v) {
                function e(e, t, n, i, o) {
                    var r, s, a, l = 0, c = "0", u = e && [], d = [], f = w, h = e || b && _.find.TAG("*", o),
                        p = A += null == f ? 1 : Math.random() || .1, g = h.length;
                    for (o && (w = t == T || t || o); c !== g && null != (r = h[c]); c++) {
                        if (b && r) {
                            for (s = 0, t || r.ownerDocument == T || (x(r), n = !k); a = m[s++];) if (a(r, t || T, n)) {
                                i.push(r);
                                break
                            }
                            o && (A = p)
                        }
                        y && ((r = !a && r) && l--, e && u.push(r))
                    }
                    if (l += c, y && c !== l) {
                        for (s = 0; a = v[s++];) a(u, d, t, n);
                        if (e) {
                            if (0 < l) for (; c--;) u[c] || d[c] || (d[c] = Y.call(i));
                            d = _e(d)
                        }
                        E.apply(i, d), o && !e && 0 < d.length && 1 < l + v.length && L.uniqueSort(i)
                    }
                    return o && (A = p, w = f), u
                }

                var y = 0 < v.length, b = 0 < m.length;
                return y ? N(e) : e
            }

            return ve.prototype = _.filters = _.pseudos, _.setFilters = new ve, h = L.tokenize = function (e, t) {
                var n, i, o, r, s, a, l, c = $[e + " "];
                if (c) return t ? 0 : c.slice(0);
                for (s = e, a = [], l = _.preFilter; s;) {
                    for (r in n && !(i = te.exec(s)) || (i && (s = s.slice(i[0].length) || s), a.push(o = [])), n = !1, (i = ne.exec(s)) && (n = i.shift(), o.push({
                        value: n,
                        type: i[0].replace(v, " ")
                    }), s = s.slice(n.length)), _.filter) !(i = b[r].exec(s)) || l[r] && !(i = l[r](i)) || (n = i.shift(), o.push({
                        value: n,
                        type: r,
                        matches: i
                    }), s = s.slice(n.length));
                    if (!n) break
                }
                return t ? s.length : s ? L.error(e) : $(e, a).slice(0)
            }, B = L.compile = function (e, t) {
                var n, i = [], o = [], r = V[e + " "];
                if (!r) {
                    for (n = (t = t || h(e)).length; n--;) ((r = function e(t) {
                        for (var i, n, o, r = t.length, s = _.relative[t[0].type], a = s || _.relative[" "], l = s ? 1 : 0, c = ye(function (e) {
                            return e === i
                        }, a, !0), u = ye(function (e) {
                            return -1 < y(i, e)
                        }, a, !0), d = [function (e, t, n) {
                            return e = !s && (n || t !== w) || ((i = t).nodeType ? c : u)(e, t, n), i = null, e
                        }]; l < r; l++) if (n = _.relative[t[l].type]) d = [ye(be(d), n)]; else {
                            if ((n = _.filter[t[l].type].apply(null, t[l].matches))[C]) {
                                for (o = ++l; o < r && !_.relative[t[o].type]; o++) ;
                                return we(1 < l && be(d), 1 < l && P(t.slice(0, l - 1).concat({value: " " === t[l - 2].type ? "*" : ""})).replace(v, "$1"), n, l < o && e(t.slice(l, o)), o < r && e(t = t.slice(o)), o < r && P(t))
                            }
                            d.push(n)
                        }
                        return be(d)
                    }(t[n]))[C] ? i : o).push(r);
                    (r = V(e, xe(o, i))).selector = e
                }
                return r
            }, F = L.select = function (e, t, n, i) {
                var o, r, s, a, l, c = "function" == typeof e && e, u = !i && h(e = c.selector || e);
                if (n = n || [], 1 === u.length) {
                    if (2 < (r = u[0] = u[0].slice(0)).length && "ID" === (s = r[0]).type && 9 === t.nodeType && k && _.relative[r[1].type]) {
                        if (!(t = (_.find.ID(s.matches[0].replace(D, d), t) || [])[0])) return n;
                        c && (t = t.parentNode), e = e.slice(r.shift().value.length)
                    }
                    for (o = b.needsContext.test(e) ? 0 : r.length; o-- && (s = r[o], !_.relative[a = s.type]);) if ((l = _.find[a]) && (i = l(s.matches[0].replace(D, d), ue.test(r[0].type) && me(t.parentNode) || t))) {
                        if (r.splice(o, 1), e = i.length && P(r)) break;
                        return E.apply(n, i), n
                    }
                }
                return (c || B(e, u))(i, t, !k, n, !t || ue.test(e) && me(t.parentNode) || t), n
            }, f.sortStable = C.split("").sort(X).join("") === C, f.detectDuplicates = !!c, x(), f.sortDetached = j(function (e) {
                return 1 & e.compareDocumentPosition(T.createElement("fieldset"))
            }), j(function (e) {
                return e.innerHTML = "<a href='#'></a>", "#" === e.firstChild.getAttribute("href")
            }) || he("type|href|height|width", function (e, t, n) {
                if (!n) return e.getAttribute(t, "type" === t.toLowerCase() ? 1 : 2)
            }), f.attributes && j(function (e) {
                return e.innerHTML = "<input/>", e.firstChild.setAttribute("value", ""), "" === e.firstChild.getAttribute("value")
            }) || he("value", function (e, t, n) {
                if (!n && "input" === e.nodeName.toLowerCase()) return e.defaultValue
            }), j(function (e) {
                return null == e.getAttribute("disabled")
            }) || he(K, function (e, t, n) {
                if (!n) return !0 === e[t] ? t.toLowerCase() : (n = e.getAttributeNode(t)) && n.specified ? n.value : null
            }), L
        }(w),
        G = (T.find = e, T.expr = e.selectors, T.expr[":"] = T.expr.pseudos, T.uniqueSort = T.unique = e.uniqueSort, T.text = e.getText, T.isXMLDoc = e.isXML, T.contains = e.contains, T.escapeSelector = e.escape, T.expr.match.needsContext);

    function l(e, t) {
        return e.nodeName && e.nodeName.toLowerCase() === t.toLowerCase()
    }

    var K = /^<([a-z][^\/\0>:\x20\t\r\n\f]*)[\x20\t\r\n\f]*\/?>(?:<\/\1>|)$/i;

    function J(e, n, i) {
        return y(n) ? T.grep(e, function (e, t) {
            return !!n.call(e, t, e) !== i
        }) : n.nodeType ? T.grep(e, function (e) {
            return e === n !== i
        }) : "string" != typeof n ? T.grep(e, function (e) {
            return -1 < B.call(n, e) !== i
        }) : T.filter(n, e, i)
    }

    T.filter = function (e, t, n) {
        var i = t[0];
        return n && (e = ":not(" + e + ")"), 1 === t.length && 1 === i.nodeType ? T.find.matchesSelector(i, e) ? [i] : [] : T.find.matches(e, T.grep(t, function (e) {
            return 1 === e.nodeType
        }))
    }, T.fn.extend({
        find: function (e) {
            var t, n, i = this.length, o = this;
            if ("string" != typeof e) return this.pushStack(T(e).filter(function () {
                for (t = 0; t < i; t++) if (T.contains(o[t], this)) return !0
            }));
            for (n = this.pushStack([]), t = 0; t < i; t++) T.find(e, o[t], n);
            return 1 < i ? T.uniqueSort(n) : n
        }, filter: function (e) {
            return this.pushStack(J(this, e || [], !1))
        }, not: function (e) {
            return this.pushStack(J(this, e || [], !0))
        }, is: function (e) {
            return !!J(this, "string" == typeof e && G.test(e) ? T(e) : e || [], !1).length
        }
    });
    var Z, ee = /^(?:\s*(<[\w\W]+>)[^>]*|#([\w-]+))$/, te = ((T.fn.init = function (e, t, n) {
            if (!e) return this;
            if (n = n || Z, "string" != typeof e) return e.nodeType ? (this[0] = e, this.length = 1, this) : y(e) ? void 0 !== n.ready ? n.ready(e) : e(T) : T.makeArray(e, this);
            if (!(i = "<" === e[0] && ">" === e[e.length - 1] && 3 <= e.length ? [null, e, null] : ee.exec(e)) || !i[1] && t) return (!t || t.jquery ? t || n : this.constructor(t)).find(e);
            if (i[1]) {
                if (t = t instanceof T ? t[0] : t, T.merge(this, T.parseHTML(i[1], t && t.nodeType ? t.ownerDocument || t : x, !0)), K.test(i[1]) && T.isPlainObject(t)) for (var i in t) y(this[i]) ? this[i](t[i]) : this.attr(i, t[i]);
                return this
            }
            return (n = x.getElementById(i[2])) && (this[0] = n, this.length = 1), this
        }).prototype = T.fn, Z = T(x), /^(?:parents|prev(?:Until|All))/),
        ne = {children: !0, contents: !0, next: !0, prev: !0};

    function ie(e, t) {
        for (; (e = e[t]) && 1 !== e.nodeType;) ;
        return e
    }

    T.fn.extend({
        has: function (e) {
            var t = T(e, this), n = t.length;
            return this.filter(function () {
                for (var e = 0; e < n; e++) if (T.contains(this, t[e])) return !0
            })
        }, closest: function (e, t) {
            var n, i = 0, o = this.length, r = [], s = "string" != typeof e && T(e);
            if (!G.test(e)) for (; i < o; i++) for (n = this[i]; n && n !== t; n = n.parentNode) if (n.nodeType < 11 && (s ? -1 < s.index(n) : 1 === n.nodeType && T.find.matchesSelector(n, e))) {
                r.push(n);
                break
            }
            return this.pushStack(1 < r.length ? T.uniqueSort(r) : r)
        }, index: function (e) {
            return e ? "string" == typeof e ? B.call(T(e), this[0]) : B.call(this, e.jquery ? e[0] : e) : this[0] && this[0].parentNode ? this.first().prevAll().length : -1
        }, add: function (e, t) {
            return this.pushStack(T.uniqueSort(T.merge(this.get(), T(e, t))))
        }, addBack: function (e) {
            return this.add(null == e ? this.prevObject : this.prevObject.filter(e))
        }
    }), T.each({
        parent: function (e) {
            e = e.parentNode;
            return e && 11 !== e.nodeType ? e : null
        }, parents: function (e) {
            return i(e, "parentNode")
        }, parentsUntil: function (e, t, n) {
            return i(e, "parentNode", n)
        }, next: function (e) {
            return ie(e, "nextSibling")
        }, prev: function (e) {
            return ie(e, "previousSibling")
        }, nextAll: function (e) {
            return i(e, "nextSibling")
        }, prevAll: function (e) {
            return i(e, "previousSibling")
        }, nextUntil: function (e, t, n) {
            return i(e, "nextSibling", n)
        }, prevUntil: function (e, t, n) {
            return i(e, "previousSibling", n)
        }, siblings: function (e) {
            return Q((e.parentNode || {}).firstChild, e)
        }, children: function (e) {
            return Q(e.firstChild)
        }, contents: function (e) {
            return null != e.contentDocument && H(e.contentDocument) ? e.contentDocument : (l(e, "template") && (e = e.content || e), T.merge([], e.childNodes))
        }
    }, function (i, o) {
        T.fn[i] = function (e, t) {
            var n = T.map(this, o, e);
            return (t = "Until" !== i.slice(-5) ? e : t) && "string" == typeof t && (n = T.filter(t, n)), 1 < this.length && (ne[i] || T.uniqueSort(n), te.test(i) && n.reverse()), this.pushStack(n)
        }
    });
    var k = /[^\x20\t\r\n\f]+/g;

    function u(e) {
        return e
    }

    function oe(e) {
        throw e
    }

    function re(e, t, n, i) {
        var o;
        try {
            e && y(o = e.promise) ? o.call(e).done(t).fail(n) : e && y(o = e.then) ? o.call(e, t, n) : t.apply(void 0, [e].slice(i))
        } catch (e) {
            n.apply(void 0, [e])
        }
    }

    T.Callbacks = function (i) {
        var e, n;
        i = "string" == typeof i ? (e = i, n = {}, T.each(e.match(k) || [], function (e, t) {
            n[t] = !0
        }), n) : T.extend({}, i);

        function o() {
            for (a = a || i.once, s = r = !0; c.length; u = -1) for (t = c.shift(); ++u < l.length;) !1 === l[u].apply(t[0], t[1]) && i.stopOnFalse && (u = l.length, t = !1);
            i.memory || (t = !1), r = !1, a && (l = t ? [] : "")
        }

        var r, t, s, a, l = [], c = [], u = -1, d = {
            add: function () {
                return l && (t && !r && (u = l.length - 1, c.push(t)), function n(e) {
                    T.each(e, function (e, t) {
                        y(t) ? i.unique && d.has(t) || l.push(t) : t && t.length && "string" !== p(t) && n(t)
                    })
                }(arguments), t && !r && o()), this
            }, remove: function () {
                return T.each(arguments, function (e, t) {
                    for (var n; -1 < (n = T.inArray(t, l, n));) l.splice(n, 1), n <= u && u--
                }), this
            }, has: function (e) {
                return e ? -1 < T.inArray(e, l) : 0 < l.length
            }, empty: function () {
                return l = l && [], this
            }, disable: function () {
                return a = c = [], l = t = "", this
            }, disabled: function () {
                return !l
            }, lock: function () {
                return a = c = [], t || r || (l = t = ""), this
            }, locked: function () {
                return !!a
            }, fireWith: function (e, t) {
                return a || (t = [e, (t = t || []).slice ? t.slice() : t], c.push(t), r || o()), this
            }, fire: function () {
                return d.fireWith(this, arguments), this
            }, fired: function () {
                return !!s
            }
        };
        return d
    }, T.extend({
        Deferred: function (e) {
            var r = [["notify", "progress", T.Callbacks("memory"), T.Callbacks("memory"), 2], ["resolve", "done", T.Callbacks("once memory"), T.Callbacks("once memory"), 0, "resolved"], ["reject", "fail", T.Callbacks("once memory"), T.Callbacks("once memory"), 1, "rejected"]],
                o = "pending", s = {
                    state: function () {
                        return o
                    }, always: function () {
                        return a.done(arguments).fail(arguments), this
                    }, catch: function (e) {
                        return s.then(null, e)
                    }, pipe: function () {
                        var o = arguments;
                        return T.Deferred(function (i) {
                            T.each(r, function (e, t) {
                                var n = y(o[t[4]]) && o[t[4]];
                                a[t[1]](function () {
                                    var e = n && n.apply(this, arguments);
                                    e && y(e.promise) ? e.promise().progress(i.notify).done(i.resolve).fail(i.reject) : i[t[0] + "With"](this, n ? [e] : arguments)
                                })
                            }), o = null
                        }).promise()
                    }, then: function (t, n, i) {
                        var l = 0;

                        function c(o, r, s, a) {
                            return function () {
                                function e() {
                                    var e, t;
                                    if (!(o < l)) {
                                        if ((e = s.apply(n, i)) === r.promise()) throw new TypeError("Thenable self-resolution");
                                        t = e && ("object" == typeof e || "function" == typeof e) && e.then, y(t) ? a ? t.call(e, c(l, r, u, a), c(l, r, oe, a)) : (l++, t.call(e, c(l, r, u, a), c(l, r, oe, a), c(l, r, u, r.notifyWith))) : (s !== u && (n = void 0, i = [e]), (a || r.resolveWith)(n, i))
                                    }
                                }

                                var n = this, i = arguments, t = a ? e : function () {
                                    try {
                                        e()
                                    } catch (e) {
                                        T.Deferred.exceptionHook && T.Deferred.exceptionHook(e, t.stackTrace), l <= o + 1 && (s !== oe && (n = void 0, i = [e]), r.rejectWith(n, i))
                                    }
                                };
                                o ? t() : (T.Deferred.getStackHook && (t.stackTrace = T.Deferred.getStackHook()), w.setTimeout(t))
                            }
                        }

                        return T.Deferred(function (e) {
                            r[0][3].add(c(0, e, y(i) ? i : u, e.notifyWith)), r[1][3].add(c(0, e, y(t) ? t : u)), r[2][3].add(c(0, e, y(n) ? n : oe))
                        }).promise()
                    }, promise: function (e) {
                        return null != e ? T.extend(e, s) : s
                    }
                }, a = {};
            return T.each(r, function (e, t) {
                var n = t[2], i = t[5];
                s[t[1]] = n.add, i && n.add(function () {
                    o = i
                }, r[3 - e][2].disable, r[3 - e][3].disable, r[0][2].lock, r[0][3].lock), n.add(t[3].fire), a[t[0]] = function () {
                    return a[t[0] + "With"](this === a ? void 0 : this, arguments), this
                }, a[t[0] + "With"] = n.fireWith
            }), s.promise(a), e && e.call(a, a), a
        }, when: function (e) {
            function t(t) {
                return function (e) {
                    o[t] = this, r[t] = 1 < arguments.length ? a.call(arguments) : e, --n || s.resolveWith(o, r)
                }
            }

            var n = arguments.length, i = n, o = Array(i), r = a.call(arguments), s = T.Deferred();
            if (n <= 1 && (re(e, s.done(t(i)).resolve, s.reject, !n), "pending" === s.state() || y(r[i] && r[i].then))) return s.then();
            for (; i--;) re(r[i], t(i), s.reject);
            return s.promise()
        }
    });
    var se = /^(Eval|Internal|Range|Reference|Syntax|Type|URI)Error$/,
        ae = (T.Deferred.exceptionHook = function (e, t) {
            w.console && w.console.warn && e && se.test(e.name) && w.console.warn("jQuery.Deferred exception: " + e.message, e.stack, t)
        }, T.readyException = function (e) {
            w.setTimeout(function () {
                throw e
            })
        }, T.Deferred());

    function le() {
        x.removeEventListener("DOMContentLoaded", le), w.removeEventListener("load", le), T.ready()
    }

    T.fn.ready = function (e) {
        return ae.then(e).catch(function (e) {
            T.readyException(e)
        }), this
    }, T.extend({
        isReady: !1, readyWait: 1, ready: function (e) {
            (!0 === e ? --T.readyWait : T.isReady) || (T.isReady = !0) !== e && 0 < --T.readyWait || ae.resolveWith(x, [T])
        }
    }), T.ready.then = ae.then, "complete" === x.readyState || "loading" !== x.readyState && !x.documentElement.doScroll ? w.setTimeout(T.ready) : (x.addEventListener("DOMContentLoaded", le), w.addEventListener("load", le));

    function d(e, t, n, i, o, r, s) {
        var a = 0, l = e.length, c = null == n;
        if ("object" === p(n)) for (a in o = !0, n) d(e, t, a, n[a], !0, r, s); else if (void 0 !== i && (o = !0, y(i) || (s = !0), t = c ? s ? (t.call(e, i), null) : (c = t, function (e, t, n) {
            return c.call(T(e), n)
        }) : t)) for (; a < l; a++) t(e[a], n, s ? i : i.call(e[a], a, t(e[a], n)));
        return o ? e : c ? t.call(e) : l ? t(e[0], n) : r
    }

    var ce = /^-ms-/, ue = /-([a-z])/g;

    function de(e, t) {
        return t.toUpperCase()
    }

    function b(e) {
        return e.replace(ce, "ms-").replace(ue, de)
    }

    function v(e) {
        return 1 === e.nodeType || 9 === e.nodeType || !+e.nodeType
    }

    function fe() {
        this.expando = T.expando + fe.uid++
    }

    fe.uid = 1, fe.prototype = {
        cache: function (e) {
            var t = e[this.expando];
            return t || (t = {}, v(e) && (e.nodeType ? e[this.expando] = t : Object.defineProperty(e, this.expando, {
                value: t,
                configurable: !0
            }))), t
        }, set: function (e, t, n) {
            var i, o = this.cache(e);
            if ("string" == typeof t) o[b(t)] = n; else for (i in t) o[b(i)] = t[i];
            return o
        }, get: function (e, t) {
            return void 0 === t ? this.cache(e) : e[this.expando] && e[this.expando][b(t)]
        }, access: function (e, t, n) {
            return void 0 === t || t && "string" == typeof t && void 0 === n ? this.get(e, t) : (this.set(e, t, n), void 0 !== n ? n : t)
        }, remove: function (e, t) {
            var n, i = e[this.expando];
            if (void 0 !== i) {
                if (void 0 !== t) {
                    n = (t = Array.isArray(t) ? t.map(b) : (t = b(t)) in i ? [t] : t.match(k) || []).length;
                    for (; n--;) delete i[t[n]]
                }
                void 0 !== t && !T.isEmptyObject(i) || (e.nodeType ? e[this.expando] = void 0 : delete e[this.expando])
            }
        }, hasData: function (e) {
            e = e[this.expando];
            return void 0 !== e && !T.isEmptyObject(e)
        }
    };
    var _ = new fe, c = new fe, he = /^(?:\{[\w\W]*\}|\[[\w\W]*\])$/, pe = /[A-Z]/g;

    function ge(e, t, n) {
        var i, o;
        if (void 0 === n && 1 === e.nodeType) if (i = "data-" + t.replace(pe, "-$&").toLowerCase(), "string" == typeof (n = e.getAttribute(i))) {
            try {
                n = "true" === (o = n) || "false" !== o && ("null" === o ? null : o === +o + "" ? +o : he.test(o) ? JSON.parse(o) : o)
            } catch (e) {
            }
            c.set(e, t, n)
        } else n = void 0;
        return n
    }

    T.extend({
        hasData: function (e) {
            return c.hasData(e) || _.hasData(e)
        }, data: function (e, t, n) {
            return c.access(e, t, n)
        }, removeData: function (e, t) {
            c.remove(e, t)
        }, _data: function (e, t, n) {
            return _.access(e, t, n)
        }, _removeData: function (e, t) {
            _.remove(e, t)
        }
    }), T.fn.extend({
        data: function (n, e) {
            var t, i, o, r = this[0], s = r && r.attributes;
            if (void 0 !== n) return "object" == typeof n ? this.each(function () {
                c.set(this, n)
            }) : d(this, function (e) {
                var t;
                if (r && void 0 === e) return void 0 !== (t = c.get(r, n)) || void 0 !== (t = ge(r, n)) ? t : void 0;
                this.each(function () {
                    c.set(this, n, e)
                })
            }, null, e, 1 < arguments.length, null, !0);
            if (this.length && (o = c.get(r), 1 === r.nodeType && !_.get(r, "hasDataAttrs"))) {
                for (t = s.length; t--;) s[t] && 0 === (i = s[t].name).indexOf("data-") && (i = b(i.slice(5)), ge(r, i, o[i]));
                _.set(r, "hasDataAttrs", !0)
            }
            return o
        }, removeData: function (e) {
            return this.each(function () {
                c.remove(this, e)
            })
        }
    }), T.extend({
        queue: function (e, t, n) {
            var i;
            if (e) return i = _.get(e, t = (t || "fx") + "queue"), n && (!i || Array.isArray(n) ? i = _.access(e, t, T.makeArray(n)) : i.push(n)), i || []
        }, dequeue: function (e, t) {
            t = t || "fx";
            var n = T.queue(e, t), i = n.length, o = n.shift(), r = T._queueHooks(e, t);
            "inprogress" === o && (o = n.shift(), i--), o && ("fx" === t && n.unshift("inprogress"), delete r.stop, o.call(e, function () {
                T.dequeue(e, t)
            }, r)), !i && r && r.empty.fire()
        }, _queueHooks: function (e, t) {
            var n = t + "queueHooks";
            return _.get(e, n) || _.access(e, n, {
                empty: T.Callbacks("once memory").add(function () {
                    _.remove(e, [t + "queue", n])
                })
            })
        }
    }), T.fn.extend({
        queue: function (t, n) {
            var e = 2;
            return "string" != typeof t && (n = t, t = "fx", e--), arguments.length < e ? T.queue(this[0], t) : void 0 === n ? this : this.each(function () {
                var e = T.queue(this, t, n);
                T._queueHooks(this, t), "fx" === t && "inprogress" !== e[0] && T.dequeue(this, t)
            })
        }, dequeue: function (e) {
            return this.each(function () {
                T.dequeue(this, e)
            })
        }, clearQueue: function (e) {
            return this.queue(e || "fx", [])
        }, promise: function (e, t) {
            function n() {
                --o || r.resolveWith(s, [s])
            }

            var i, o = 1, r = T.Deferred(), s = this, a = this.length;
            for ("string" != typeof e && (t = e, e = void 0), e = e || "fx"; a--;) (i = _.get(s[a], e + "queueHooks")) && i.empty && (o++, i.empty.add(n));
            return n(), r.promise(t)
        }
    });

    function me(e, t) {
        return "none" === (e = t || e).style.display || "" === e.style.display && A(e) && "none" === T.css(e, "display")
    }

    var e = /[+-]?(?:\d*\.|)\d+(?:[eE][+-]?\d+|)/.source, ve = new RegExp("^(?:([+-])=|)(" + e + ")([a-z%]*)$", "i"),
        f = ["Top", "Right", "Bottom", "Left"], C = x.documentElement, A = function (e) {
            return T.contains(e.ownerDocument, e)
        }, ye = {composed: !0};
    C.getRootNode && (A = function (e) {
        return T.contains(e.ownerDocument, e) || e.getRootNode(ye) === e.ownerDocument
    });

    function be(e, t, n, i) {
        var o, r, s = 20, a = i ? function () {
                return i.cur()
            } : function () {
                return T.css(e, t, "")
            }, l = a(), c = n && n[3] || (T.cssNumber[t] ? "" : "px"),
            u = e.nodeType && (T.cssNumber[t] || "px" !== c && +l) && ve.exec(T.css(e, t));
        if (u && u[3] !== c) {
            for (c = c || u[3], u = +(l /= 2) || 1; s--;) T.style(e, t, u + c), (1 - r) * (1 - (r = a() / l || .5)) <= 0 && (s = 0), u /= r;
            T.style(e, t, (u *= 2) + c), n = n || []
        }
        return n && (u = +u || +l || 0, o = n[1] ? u + (n[1] + 1) * n[2] : +n[2], i && (i.unit = c, i.start = u, i.end = o)), o
    }

    var _e = {};

    function E(e, t) {
        for (var n, i, o, r, s, a = [], l = 0, c = e.length; l < c; l++) (i = e[l]).style && (n = i.style.display, t ? ("none" === n && (a[l] = _.get(i, "display") || null, a[l] || (i.style.display = "")), "" === i.style.display && me(i) && (a[l] = (s = r = void 0, r = (o = i).ownerDocument, o = o.nodeName, (s = _e[o]) || (r = r.body.appendChild(r.createElement(o)), s = T.css(r, "display"), r.parentNode.removeChild(r), _e[o] = s = "none" === s ? "block" : s)))) : "none" !== n && (a[l] = "none", _.set(i, "display", n)));
        for (l = 0; l < c; l++) null != a[l] && (e[l].style.display = a[l]);
        return e
    }

    T.fn.extend({
        show: function () {
            return E(this, !0)
        }, hide: function () {
            return E(this)
        }, toggle: function (e) {
            return "boolean" == typeof e ? e ? this.show() : this.hide() : this.each(function () {
                me(this) ? T(this).show() : T(this).hide()
            })
        }
    });
    var we = /^(?:checkbox|radio)$/i, xe = /<([a-z][^\/\0>\x20\t\r\n\f]*)/i, Te = /^$|^module$|\/(?:java|ecma)script/i,
        S = (N = x.createDocumentFragment().appendChild(x.createElement("div")), (r = x.createElement("input")).setAttribute("type", "radio"), r.setAttribute("checked", "checked"), r.setAttribute("name", "t"), N.appendChild(r), m.checkClone = N.cloneNode(!0).cloneNode(!0).lastChild.checked, N.innerHTML = "<textarea>x</textarea>", m.noCloneChecked = !!N.cloneNode(!0).lastChild.defaultValue, N.innerHTML = "<option></option>", m.option = !!N.lastChild, {
            thead: [1, "<table>", "</table>"],
            col: [2, "<table><colgroup>", "</colgroup></table>"],
            tr: [2, "<table><tbody>", "</tbody></table>"],
            td: [3, "<table><tbody><tr>", "</tr></tbody></table>"],
            _default: [0, "", ""]
        });

    function D(e, t) {
        var n = void 0 !== e.getElementsByTagName ? e.getElementsByTagName(t || "*") : void 0 !== e.querySelectorAll ? e.querySelectorAll(t || "*") : [];
        return void 0 === t || t && l(e, t) ? T.merge([e], n) : n
    }

    function ke(e, t) {
        for (var n = 0, i = e.length; n < i; n++) _.set(e[n], "globalEval", !t || _.get(t[n], "globalEval"))
    }

    S.tbody = S.tfoot = S.colgroup = S.caption = S.thead, S.th = S.td, m.option || (S.optgroup = S.option = [1, "<select multiple='multiple'>", "</select>"]);
    var Ce = /<|&#?\w+;/;

    function Ae(e, t, n, i, o) {
        for (var r, s, a, l, c, u = t.createDocumentFragment(), d = [], f = 0, h = e.length; f < h; f++) if ((r = e[f]) || 0 === r) if ("object" === p(r)) T.merge(d, r.nodeType ? [r] : r); else if (Ce.test(r)) {
            for (s = s || u.appendChild(t.createElement("div")), a = (xe.exec(r) || ["", ""])[1].toLowerCase(), a = S[a] || S._default, s.innerHTML = a[1] + T.htmlPrefilter(r) + a[2], c = a[0]; c--;) s = s.lastChild;
            T.merge(d, s.childNodes), (s = u.firstChild).textContent = ""
        } else d.push(t.createTextNode(r));
        for (u.textContent = "", f = 0; r = d[f++];) if (i && -1 < T.inArray(r, i)) o && o.push(r); else if (l = A(r), s = D(u.appendChild(r), "script"), l && ke(s), n) for (c = 0; r = s[c++];) Te.test(r.type || "") && n.push(r);
        return u
    }

    var Ee = /^([^.]*)(?:\.(.+)|)/;

    function n() {
        return !0
    }

    function h() {
        return !1
    }

    function Se(e, t) {
        return e === function () {
            try {
                return x.activeElement
            } catch (e) {
            }
        }() == ("focus" === t)
    }

    function De(e, t, n, i, o, r) {
        var s, a;
        if ("object" == typeof t) {
            for (a in "string" != typeof n && (i = i || n, n = void 0), t) De(e, a, n, i, t[a], r);
            return e
        }
        if (null == i && null == o ? (o = n, i = n = void 0) : null == o && ("string" == typeof n ? (o = i, i = void 0) : (o = i, i = n, n = void 0)), !1 === o) o = h; else if (!o) return e;
        return 1 === r && (s = o, (o = function (e) {
            return T().off(e), s.apply(this, arguments)
        }).guid = s.guid || (s.guid = T.guid++)), e.each(function () {
            T.event.add(this, t, o, i, n)
        })
    }

    function Le(e, o, r) {
        r ? (_.set(e, o, !1), T.event.add(e, o, {
            namespace: !1, handler: function (e) {
                var t, n, i = _.get(this, o);
                if (1 & e.isTrigger && this[o]) {
                    if (i.length) (T.event.special[o] || {}).delegateType && e.stopPropagation(); else if (i = a.call(arguments), _.set(this, o, i), t = r(this, o), this[o](), i !== (n = _.get(this, o)) || t ? _.set(this, o, !1) : n = {}, i !== n) return e.stopImmediatePropagation(), e.preventDefault(), n && n.value
                } else i.length && (_.set(this, o, {value: T.event.trigger(T.extend(i[0], T.Event.prototype), i.slice(1), this)}), e.stopImmediatePropagation())
            }
        })) : void 0 === _.get(e, o) && T.event.add(e, o, n)
    }

    T.event = {
        global: {}, add: function (t, e, n, i, o) {
            var r, s, a, l, c, u, d, f, h, p = _.get(t);
            if (v(t)) for (n.handler && (n = (r = n).handler, o = r.selector), o && T.find.matchesSelector(C, o), n.guid || (n.guid = T.guid++), (a = p.events) || (a = p.events = Object.create(null)), (s = p.handle) || (s = p.handle = function (e) {
                return void 0 !== T && T.event.triggered !== e.type ? T.event.dispatch.apply(t, arguments) : void 0
            }), l = (e = (e || "").match(k) || [""]).length; l--;) d = h = (f = Ee.exec(e[l]) || [])[1], f = (f[2] || "").split(".").sort(), d && (c = T.event.special[d] || {}, d = (o ? c.delegateType : c.bindType) || d, c = T.event.special[d] || {}, h = T.extend({
                type: d,
                origType: h,
                data: i,
                handler: n,
                guid: n.guid,
                selector: o,
                needsContext: o && T.expr.match.needsContext.test(o),
                namespace: f.join(".")
            }, r), (u = a[d]) || ((u = a[d] = []).delegateCount = 0, c.setup && !1 !== c.setup.call(t, i, f, s) || t.addEventListener && t.addEventListener(d, s)), c.add && (c.add.call(t, h), h.handler.guid || (h.handler.guid = n.guid)), o ? u.splice(u.delegateCount++, 0, h) : u.push(h), T.event.global[d] = !0)
        }, remove: function (e, t, n, i, o) {
            var r, s, a, l, c, u, d, f, h, p, g, m = _.hasData(e) && _.get(e);
            if (m && (l = m.events)) {
                for (c = (t = (t || "").match(k) || [""]).length; c--;) if (h = g = (a = Ee.exec(t[c]) || [])[1], p = (a[2] || "").split(".").sort(), h) {
                    for (d = T.event.special[h] || {}, f = l[h = (i ? d.delegateType : d.bindType) || h] || [], a = a[2] && new RegExp("(^|\\.)" + p.join("\\.(?:.*\\.|)") + "(\\.|$)"), s = r = f.length; r--;) u = f[r], !o && g !== u.origType || n && n.guid !== u.guid || a && !a.test(u.namespace) || i && i !== u.selector && ("**" !== i || !u.selector) || (f.splice(r, 1), u.selector && f.delegateCount--, d.remove && d.remove.call(e, u));
                    s && !f.length && (d.teardown && !1 !== d.teardown.call(e, p, m.handle) || T.removeEvent(e, h, m.handle), delete l[h])
                } else for (h in l) T.event.remove(e, h + t[c], n, i, !0);
                T.isEmptyObject(l) && _.remove(e, "handle events")
            }
        }, dispatch: function (e) {
            var t, n, i, o, r, s = new Array(arguments.length), a = T.event.fix(e),
                e = (_.get(this, "events") || Object.create(null))[a.type] || [], l = T.event.special[a.type] || {};
            for (s[0] = a, t = 1; t < arguments.length; t++) s[t] = arguments[t];
            if (a.delegateTarget = this, !l.preDispatch || !1 !== l.preDispatch.call(this, a)) {
                for (r = T.event.handlers.call(this, a, e), t = 0; (i = r[t++]) && !a.isPropagationStopped();) for (a.currentTarget = i.elem, n = 0; (o = i.handlers[n++]) && !a.isImmediatePropagationStopped();) a.rnamespace && !1 !== o.namespace && !a.rnamespace.test(o.namespace) || (a.handleObj = o, a.data = o.data, void 0 !== (o = ((T.event.special[o.origType] || {}).handle || o.handler).apply(i.elem, s)) && !1 === (a.result = o) && (a.preventDefault(), a.stopPropagation()));
                return l.postDispatch && l.postDispatch.call(this, a), a.result
            }
        }, handlers: function (e, t) {
            var n, i, o, r, s, a = [], l = t.delegateCount, c = e.target;
            if (l && c.nodeType && !("click" === e.type && 1 <= e.button)) for (; c !== this; c = c.parentNode || this) if (1 === c.nodeType && ("click" !== e.type || !0 !== c.disabled)) {
                for (r = [], s = {}, n = 0; n < l; n++) void 0 === s[o = (i = t[n]).selector + " "] && (s[o] = i.needsContext ? -1 < T(o, this).index(c) : T.find(o, this, null, [c]).length), s[o] && r.push(i);
                r.length && a.push({elem: c, handlers: r})
            }
            return c = this, l < t.length && a.push({elem: c, handlers: t.slice(l)}), a
        }, addProp: function (t, e) {
            Object.defineProperty(T.Event.prototype, t, {
                enumerable: !0, configurable: !0, get: y(e) ? function () {
                    if (this.originalEvent) return e(this.originalEvent)
                } : function () {
                    if (this.originalEvent) return this.originalEvent[t]
                }, set: function (e) {
                    Object.defineProperty(this, t, {enumerable: !0, configurable: !0, writable: !0, value: e})
                }
            })
        }, fix: function (e) {
            return e[T.expando] ? e : new T.Event(e)
        }, special: {
            load: {noBubble: !0}, click: {
                setup: function (e) {
                    e = this || e;
                    return we.test(e.type) && e.click && l(e, "input") && Le(e, "click", n), !1
                }, trigger: function (e) {
                    e = this || e;
                    return we.test(e.type) && e.click && l(e, "input") && Le(e, "click"), !0
                }, _default: function (e) {
                    e = e.target;
                    return we.test(e.type) && e.click && l(e, "input") && _.get(e, "click") || l(e, "a")
                }
            }, beforeunload: {
                postDispatch: function (e) {
                    void 0 !== e.result && e.originalEvent && (e.originalEvent.returnValue = e.result)
                }
            }
        }
    }, T.removeEvent = function (e, t, n) {
        e.removeEventListener && e.removeEventListener(t, n)
    }, T.Event = function (e, t) {
        if (!(this instanceof T.Event)) return new T.Event(e, t);
        e && e.type ? (this.originalEvent = e, this.type = e.type, this.isDefaultPrevented = e.defaultPrevented || void 0 === e.defaultPrevented && !1 === e.returnValue ? n : h, this.target = e.target && 3 === e.target.nodeType ? e.target.parentNode : e.target, this.currentTarget = e.currentTarget, this.relatedTarget = e.relatedTarget) : this.type = e, t && T.extend(this, t), this.timeStamp = e && e.timeStamp || Date.now(), this[T.expando] = !0
    }, T.Event.prototype = {
        constructor: T.Event,
        isDefaultPrevented: h,
        isPropagationStopped: h,
        isImmediatePropagationStopped: h,
        isSimulated: !1,
        preventDefault: function () {
            var e = this.originalEvent;
            this.isDefaultPrevented = n, e && !this.isSimulated && e.preventDefault()
        },
        stopPropagation: function () {
            var e = this.originalEvent;
            this.isPropagationStopped = n, e && !this.isSimulated && e.stopPropagation()
        },
        stopImmediatePropagation: function () {
            var e = this.originalEvent;
            this.isImmediatePropagationStopped = n, e && !this.isSimulated && e.stopImmediatePropagation(), this.stopPropagation()
        }
    }, T.each({
        altKey: !0,
        bubbles: !0,
        cancelable: !0,
        changedTouches: !0,
        ctrlKey: !0,
        detail: !0,
        eventPhase: !0,
        metaKey: !0,
        pageX: !0,
        pageY: !0,
        shiftKey: !0,
        view: !0,
        char: !0,
        code: !0,
        charCode: !0,
        key: !0,
        keyCode: !0,
        button: !0,
        buttons: !0,
        clientX: !0,
        clientY: !0,
        offsetX: !0,
        offsetY: !0,
        pointerId: !0,
        pointerType: !0,
        screenX: !0,
        screenY: !0,
        targetTouches: !0,
        toElement: !0,
        touches: !0,
        which: !0
    }, T.event.addProp), T.each({focus: "focusin", blur: "focusout"}, function (t, e) {
        T.event.special[t] = {
            setup: function () {
                return Le(this, t, Se), !1
            }, trigger: function () {
                return Le(this, t), !0
            }, _default: function (e) {
                return _.get(e.target, t)
            }, delegateType: e
        }
    }), T.each({
        mouseenter: "mouseover",
        mouseleave: "mouseout",
        pointerenter: "pointerover",
        pointerleave: "pointerout"
    }, function (e, o) {
        T.event.special[e] = {
            delegateType: o, bindType: o, handle: function (e) {
                var t, n = e.relatedTarget, i = e.handleObj;
                return n && (n === this || T.contains(this, n)) || (e.type = i.origType, t = i.handler.apply(this, arguments), e.type = o), t
            }
        }
    }), T.fn.extend({
        on: function (e, t, n, i) {
            return De(this, e, t, n, i)
        }, one: function (e, t, n, i) {
            return De(this, e, t, n, i, 1)
        }, off: function (e, t, n) {
            var i, o;
            if (e && e.preventDefault && e.handleObj) return i = e.handleObj, T(e.delegateTarget).off(i.namespace ? i.origType + "." + i.namespace : i.origType, i.selector, i.handler), this;
            if ("object" != typeof e) return !1 !== t && "function" != typeof t || (n = t, t = void 0), !1 === n && (n = h), this.each(function () {
                T.event.remove(this, e, n, t)
            });
            for (o in e) this.off(o, t, e[o]);
            return this
        }
    });
    var Oe = /<script|<style|<link/i, Ne = /checked\s*(?:[^=]|=\s*.checked.)/i, je = /^\s*<!\[CDATA\[|\]\]>\s*$/g;

    function Ie(e, t) {
        return l(e, "table") && l(11 !== t.nodeType ? t : t.firstChild, "tr") && T(e).children("tbody")[0] || e
    }

    function Pe(e) {
        return e.type = (null !== e.getAttribute("type")) + "/" + e.type, e
    }

    function Me(e) {
        return "true/" === (e.type || "").slice(0, 5) ? e.type = e.type.slice(5) : e.removeAttribute("type"), e
    }

    function He(e, t) {
        var n, i, o, r;
        if (1 === t.nodeType) {
            if (_.hasData(e) && (r = _.get(e).events)) for (o in _.remove(t, "handle events"), r) for (n = 0, i = r[o].length; n < i; n++) T.event.add(t, o, r[o][n]);
            c.hasData(e) && (e = c.access(e), e = T.extend({}, e), c.set(t, e))
        }
    }

    function L(n, i, o, r) {
        i = q(i);
        var e, t, s, a, l, c, u = 0, d = n.length, f = d - 1, h = i[0], p = y(h);
        if (p || 1 < d && "string" == typeof h && !m.checkClone && Ne.test(h)) return n.each(function (e) {
            var t = n.eq(e);
            p && (i[0] = h.call(this, e, t.html())), L(t, i, o, r)
        });
        if (d && (t = (e = Ae(i, n[0].ownerDocument, !1, n, r)).firstChild, 1 === e.childNodes.length && (e = t), t || r)) {
            for (a = (s = T.map(D(e, "script"), Pe)).length; u < d; u++) l = e, u !== f && (l = T.clone(l, !0, !0), a && T.merge(s, D(l, "script"))), o.call(n[u], l, u);
            if (a) for (c = s[s.length - 1].ownerDocument, T.map(s, Me), u = 0; u < a; u++) l = s[u], Te.test(l.type || "") && !_.access(l, "globalEval") && T.contains(c, l) && (l.src && "module" !== (l.type || "").toLowerCase() ? T._evalUrl && !l.noModule && T._evalUrl(l.src, {nonce: l.nonce || l.getAttribute("nonce")}, c) : U(l.textContent.replace(je, ""), l, c))
        }
        return n
    }

    function qe(e, t, n) {
        for (var i, o = t ? T.filter(t, e) : e, r = 0; null != (i = o[r]); r++) n || 1 !== i.nodeType || T.cleanData(D(i)), i.parentNode && (n && A(i) && ke(D(i, "script")), i.parentNode.removeChild(i));
        return e
    }

    T.extend({
        htmlPrefilter: function (e) {
            return e
        }, clone: function (e, t, n) {
            var i, o, r, s, a, l, c, u = e.cloneNode(!0), d = A(e);
            if (!(m.noCloneChecked || 1 !== e.nodeType && 11 !== e.nodeType || T.isXMLDoc(e))) for (s = D(u), i = 0, o = (r = D(e)).length; i < o; i++) a = r[i], l = s[i], c = void 0, "input" === (c = l.nodeName.toLowerCase()) && we.test(a.type) ? l.checked = a.checked : "input" !== c && "textarea" !== c || (l.defaultValue = a.defaultValue);
            if (t) if (n) for (r = r || D(e), s = s || D(u), i = 0, o = r.length; i < o; i++) He(r[i], s[i]); else He(e, u);
            return 0 < (s = D(u, "script")).length && ke(s, !d && D(e, "script")), u
        }, cleanData: function (e) {
            for (var t, n, i, o = T.event.special, r = 0; void 0 !== (n = e[r]); r++) if (v(n)) {
                if (t = n[_.expando]) {
                    if (t.events) for (i in t.events) o[i] ? T.event.remove(n, i) : T.removeEvent(n, i, t.handle);
                    n[_.expando] = void 0
                }
                n[c.expando] && (n[c.expando] = void 0)
            }
        }
    }), T.fn.extend({
        detach: function (e) {
            return qe(this, e, !0)
        }, remove: function (e) {
            return qe(this, e)
        }, text: function (e) {
            return d(this, function (e) {
                return void 0 === e ? T.text(this) : this.empty().each(function () {
                    1 !== this.nodeType && 11 !== this.nodeType && 9 !== this.nodeType || (this.textContent = e)
                })
            }, null, e, arguments.length)
        }, append: function () {
            return L(this, arguments, function (e) {
                1 !== this.nodeType && 11 !== this.nodeType && 9 !== this.nodeType || Ie(this, e).appendChild(e)
            })
        }, prepend: function () {
            return L(this, arguments, function (e) {
                var t;
                1 !== this.nodeType && 11 !== this.nodeType && 9 !== this.nodeType || (t = Ie(this, e)).insertBefore(e, t.firstChild)
            })
        }, before: function () {
            return L(this, arguments, function (e) {
                this.parentNode && this.parentNode.insertBefore(e, this)
            })
        }, after: function () {
            return L(this, arguments, function (e) {
                this.parentNode && this.parentNode.insertBefore(e, this.nextSibling)
            })
        }, empty: function () {
            for (var e, t = 0; null != (e = this[t]); t++) 1 === e.nodeType && (T.cleanData(D(e, !1)), e.textContent = "");
            return this
        }, clone: function (e, t) {
            return e = null != e && e, t = null == t ? e : t, this.map(function () {
                return T.clone(this, e, t)
            })
        }, html: function (e) {
            return d(this, function (e) {
                var t = this[0] || {}, n = 0, i = this.length;
                if (void 0 === e && 1 === t.nodeType) return t.innerHTML;
                if ("string" == typeof e && !Oe.test(e) && !S[(xe.exec(e) || ["", ""])[1].toLowerCase()]) {
                    e = T.htmlPrefilter(e);
                    try {
                        for (; n < i; n++) 1 === (t = this[n] || {}).nodeType && (T.cleanData(D(t, !1)), t.innerHTML = e);
                        t = 0
                    } catch (e) {
                    }
                }
                t && this.empty().append(e)
            }, null, e, arguments.length)
        }, replaceWith: function () {
            var n = [];
            return L(this, arguments, function (e) {
                var t = this.parentNode;
                T.inArray(this, n) < 0 && (T.cleanData(D(this)), t && t.replaceChild(e, this))
            }, n)
        }
    }), T.each({
        appendTo: "append",
        prependTo: "prepend",
        insertBefore: "before",
        insertAfter: "after",
        replaceAll: "replaceWith"
    }, function (e, s) {
        T.fn[e] = function (e) {
            for (var t, n = [], i = T(e), o = i.length - 1, r = 0; r <= o; r++) t = r === o ? this : this.clone(!0), T(i[r])[s](t), R.apply(n, t.get());
            return this.pushStack(n)
        }
    });

    function Re(e) {
        var t = e.ownerDocument.defaultView;
        return (t = t && t.opener ? t : w).getComputedStyle(e)
    }

    function Be(e, t, n) {
        var i, o = {};
        for (i in t) o[i] = e.style[i], e.style[i] = t[i];
        for (i in n = n.call(e), t) e.style[i] = o[i];
        return n
    }

    var Fe, We, ze, $e, Ve, Xe, Ue, o, Ye = new RegExp("^(" + e + ")(?!px)[a-z%]+$", "i"), Qe = /^--/,
        Ge = new RegExp(f.join("|"), "i"), r = "[\\x20\\t\\r\\n\\f]",
        Ke = new RegExp("^" + r + "+|((?:^|[^\\\\])(?:\\\\.)*)" + r + "+$", "g");

    function Je() {
        var e;
        o && (Ue.style.cssText = "position:absolute;left:-11111px;width:60px;margin-top:1px;padding:0;border:0", o.style.cssText = "position:relative;display:block;box-sizing:border-box;overflow:scroll;margin:auto;border:1px;padding:1px;width:60%;top:1%", C.appendChild(Ue).appendChild(o), e = w.getComputedStyle(o), Fe = "1%" !== e.top, Xe = 12 === Ze(e.marginLeft), o.style.right = "60%", $e = 36 === Ze(e.right), We = 36 === Ze(e.width), o.style.position = "absolute", ze = 12 === Ze(o.offsetWidth / 3), C.removeChild(Ue), o = null)
    }

    function Ze(e) {
        return Math.round(parseFloat(e))
    }

    function et(e, t, n) {
        var i, o = Qe.test(t), r = e.style;
        return (n = n || Re(e)) && (i = n.getPropertyValue(t) || n[t], "" !== (i = o ? i.replace(Ke, "$1") : i) || A(e) || (i = T.style(e, t)), !m.pixelBoxStyles() && Ye.test(i) && Ge.test(t) && (o = r.width, e = r.minWidth, t = r.maxWidth, r.minWidth = r.maxWidth = r.width = i, i = n.width, r.width = o, r.minWidth = e, r.maxWidth = t)), void 0 !== i ? i + "" : i
    }

    function tt(e, t) {
        return {
            get: function () {
                if (!e()) return (this.get = t).apply(this, arguments);
                delete this.get
            }
        }
    }

    Ue = x.createElement("div"), (o = x.createElement("div")).style && (o.style.backgroundClip = "content-box", o.cloneNode(!0).style.backgroundClip = "", m.clearCloneStyle = "content-box" === o.style.backgroundClip, T.extend(m, {
        boxSizingReliable: function () {
            return Je(), We
        }, pixelBoxStyles: function () {
            return Je(), $e
        }, pixelPosition: function () {
            return Je(), Fe
        }, reliableMarginLeft: function () {
            return Je(), Xe
        }, scrollboxSize: function () {
            return Je(), ze
        }, reliableTrDimensions: function () {
            var e, t, n;
            return null == Ve && (e = x.createElement("table"), t = x.createElement("tr"), n = x.createElement("div"), e.style.cssText = "position:absolute;left:-11111px;border-collapse:separate", t.style.cssText = "border:1px solid", t.style.height = "1px", n.style.height = "9px", n.style.display = "block", C.appendChild(e).appendChild(t).appendChild(n), n = w.getComputedStyle(t), Ve = parseInt(n.height, 10) + parseInt(n.borderTopWidth, 10) + parseInt(n.borderBottomWidth, 10) === t.offsetHeight, C.removeChild(e)), Ve
        }
    }));
    var nt = ["Webkit", "Moz", "ms"], it = x.createElement("div").style, ot = {};

    function rt(e) {
        var t = T.cssProps[e] || ot[e];
        return t || (e in it ? e : ot[e] = function (e) {
            for (var t = e[0].toUpperCase() + e.slice(1), n = nt.length; n--;) if ((e = nt[n] + t) in it) return e
        }(e) || e)
    }

    var st = /^(none|table(?!-c[ea]).+)/, at = {position: "absolute", visibility: "hidden", display: "block"},
        lt = {letterSpacing: "0", fontWeight: "400"};

    function ct(e, t, n) {
        var i = ve.exec(t);
        return i ? Math.max(0, i[2] - (n || 0)) + (i[3] || "px") : t
    }

    function ut(e, t, n, i, o, r) {
        var s = "width" === t ? 1 : 0, a = 0, l = 0;
        if (n === (i ? "border" : "content")) return 0;
        for (; s < 4; s += 2) "margin" === n && (l += T.css(e, n + f[s], !0, o)), i ? ("content" === n && (l -= T.css(e, "padding" + f[s], !0, o)), "margin" !== n && (l -= T.css(e, "border" + f[s] + "Width", !0, o))) : (l += T.css(e, "padding" + f[s], !0, o), "padding" !== n ? l += T.css(e, "border" + f[s] + "Width", !0, o) : a += T.css(e, "border" + f[s] + "Width", !0, o));
        return !i && 0 <= r && (l += Math.max(0, Math.ceil(e["offset" + t[0].toUpperCase() + t.slice(1)] - r - l - a - .5)) || 0), l
    }

    function dt(e, t, n) {
        var i = Re(e), o = (!m.boxSizingReliable() || n) && "border-box" === T.css(e, "boxSizing", !1, i), r = o,
            s = et(e, t, i), a = "offset" + t[0].toUpperCase() + t.slice(1);
        if (Ye.test(s)) {
            if (!n) return s;
            s = "auto"
        }
        return (!m.boxSizingReliable() && o || !m.reliableTrDimensions() && l(e, "tr") || "auto" === s || !parseFloat(s) && "inline" === T.css(e, "display", !1, i)) && e.getClientRects().length && (o = "border-box" === T.css(e, "boxSizing", !1, i), (r = a in e) && (s = e[a])), (s = parseFloat(s) || 0) + ut(e, t, n || (o ? "border" : "content"), r, i, s) + "px"
    }

    function s(e, t, n, i, o) {
        return new s.prototype.init(e, t, n, i, o)
    }

    T.extend({
        cssHooks: {
            opacity: {
                get: function (e, t) {
                    if (t) return "" === (t = et(e, "opacity")) ? "1" : t
                }
            }
        },
        cssNumber: {
            animationIterationCount: !0,
            columnCount: !0,
            fillOpacity: !0,
            flexGrow: !0,
            flexShrink: !0,
            fontWeight: !0,
            gridArea: !0,
            gridColumn: !0,
            gridColumnEnd: !0,
            gridColumnStart: !0,
            gridRow: !0,
            gridRowEnd: !0,
            gridRowStart: !0,
            lineHeight: !0,
            opacity: !0,
            order: !0,
            orphans: !0,
            widows: !0,
            zIndex: !0,
            zoom: !0
        },
        cssProps: {},
        style: function (e, t, n, i) {
            if (e && 3 !== e.nodeType && 8 !== e.nodeType && e.style) {
                var o, r, s, a = b(t), l = Qe.test(t), c = e.style;
                if (l || (t = rt(a)), s = T.cssHooks[t] || T.cssHooks[a], void 0 === n) return s && "get" in s && void 0 !== (o = s.get(e, !1, i)) ? o : c[t];
                "string" === (r = typeof n) && (o = ve.exec(n)) && o[1] && (n = be(e, t, o), r = "number"), null != n && n == n && ("number" !== r || l || (n += o && o[3] || (T.cssNumber[a] ? "" : "px")), m.clearCloneStyle || "" !== n || 0 !== t.indexOf("background") || (c[t] = "inherit"), s && "set" in s && void 0 === (n = s.set(e, n, i)) || (l ? c.setProperty(t, n) : c[t] = n))
            }
        },
        css: function (e, t, n, i) {
            var o, r = b(t);
            return Qe.test(t) || (t = rt(r)), "normal" === (o = void 0 === (o = (r = T.cssHooks[t] || T.cssHooks[r]) && "get" in r ? r.get(e, !0, n) : o) ? et(e, t, i) : o) && t in lt && (o = lt[t]), "" === n || n ? (r = parseFloat(o), !0 === n || isFinite(r) ? r || 0 : o) : o
        }
    }), T.each(["height", "width"], function (e, s) {
        T.cssHooks[s] = {
            get: function (e, t, n) {
                if (t) return !st.test(T.css(e, "display")) || e.getClientRects().length && e.getBoundingClientRect().width ? dt(e, s, n) : Be(e, at, function () {
                    return dt(e, s, n)
                })
            }, set: function (e, t, n) {
                var i = Re(e), o = !m.scrollboxSize() && "absolute" === i.position,
                    r = (o || n) && "border-box" === T.css(e, "boxSizing", !1, i), n = n ? ut(e, s, n, r, i) : 0;
                return r && o && (n -= Math.ceil(e["offset" + s[0].toUpperCase() + s.slice(1)] - parseFloat(i[s]) - ut(e, s, "border", !1, i) - .5)), n && (r = ve.exec(t)) && "px" !== (r[3] || "px") && (e.style[s] = t, t = T.css(e, s)), ct(0, t, n)
            }
        }
    }), T.cssHooks.marginLeft = tt(m.reliableMarginLeft, function (e, t) {
        if (t) return (parseFloat(et(e, "marginLeft")) || e.getBoundingClientRect().left - Be(e, {marginLeft: 0}, function () {
            return e.getBoundingClientRect().left
        })) + "px"
    }), T.each({margin: "", padding: "", border: "Width"}, function (o, r) {
        T.cssHooks[o + r] = {
            expand: function (e) {
                for (var t = 0, n = {}, i = "string" == typeof e ? e.split(" ") : [e]; t < 4; t++) n[o + f[t] + r] = i[t] || i[t - 2] || i[0];
                return n
            }
        }, "margin" !== o && (T.cssHooks[o + r].set = ct)
    }), T.fn.extend({
        css: function (e, t) {
            return d(this, function (e, t, n) {
                var i, o, r = {}, s = 0;
                if (Array.isArray(t)) {
                    for (i = Re(e), o = t.length; s < o; s++) r[t[s]] = T.css(e, t[s], !1, i);
                    return r
                }
                return void 0 !== n ? T.style(e, t, n) : T.css(e, t)
            }, e, t, 1 < arguments.length)
        }
    }), ((T.Tween = s).prototype = {
        constructor: s, init: function (e, t, n, i, o, r) {
            this.elem = e, this.prop = n, this.easing = o || T.easing._default, this.options = t, this.start = this.now = this.cur(), this.end = i, this.unit = r || (T.cssNumber[n] ? "" : "px")
        }, cur: function () {
            var e = s.propHooks[this.prop];
            return (e && e.get ? e : s.propHooks._default).get(this)
        }, run: function (e) {
            var t, n = s.propHooks[this.prop];
            return this.options.duration ? this.pos = t = T.easing[this.easing](e, this.options.duration * e, 0, 1, this.options.duration) : this.pos = t = e, this.now = (this.end - this.start) * t + this.start, this.options.step && this.options.step.call(this.elem, this.now, this), (n && n.set ? n : s.propHooks._default).set(this), this
        }
    }).init.prototype = s.prototype, (s.propHooks = {
        _default: {
            get: function (e) {
                return 1 !== e.elem.nodeType || null != e.elem[e.prop] && null == e.elem.style[e.prop] ? e.elem[e.prop] : (e = T.css(e.elem, e.prop, "")) && "auto" !== e ? e : 0
            }, set: function (e) {
                T.fx.step[e.prop] ? T.fx.step[e.prop](e) : 1 !== e.elem.nodeType || !T.cssHooks[e.prop] && null == e.elem.style[rt(e.prop)] ? e.elem[e.prop] = e.now : T.style(e.elem, e.prop, e.now + e.unit)
            }
        }
    }).scrollTop = s.propHooks.scrollLeft = {
        set: function (e) {
            e.elem.nodeType && e.elem.parentNode && (e.elem[e.prop] = e.now)
        }
    }, T.easing = {
        linear: function (e) {
            return e
        }, swing: function (e) {
            return .5 - Math.cos(e * Math.PI) / 2
        }, _default: "swing"
    }, T.fx = s.prototype.init, T.fx.step = {};
    var O, ft, N, ht = /^(?:toggle|show|hide)$/, pt = /queueHooks$/;

    function gt() {
        ft && (!1 === x.hidden && w.requestAnimationFrame ? w.requestAnimationFrame(gt) : w.setTimeout(gt, T.fx.interval), T.fx.tick())
    }

    function mt() {
        return w.setTimeout(function () {
            O = void 0
        }), O = Date.now()
    }

    function vt(e, t) {
        var n, i = 0, o = {height: e};
        for (t = t ? 1 : 0; i < 4; i += 2 - t) o["margin" + (n = f[i])] = o["padding" + n] = e;
        return t && (o.opacity = o.width = e), o
    }

    function yt(e, t, n) {
        for (var i, o = (j.tweeners[t] || []).concat(j.tweeners["*"]), r = 0, s = o.length; r < s; r++) if (i = o[r].call(n, t, e)) return i
    }

    function j(o, e, t) {
        var n, r, i, s, a, l, c, u = 0, d = j.prefilters.length, f = T.Deferred().always(function () {
            delete h.elem
        }), h = function () {
            if (r) return !1;
            for (var e = O || mt(), e = Math.max(0, p.startTime + p.duration - e), t = 1 - (e / p.duration || 0), n = 0, i = p.tweens.length; n < i; n++) p.tweens[n].run(t);
            return f.notifyWith(o, [p, t, e]), t < 1 && i ? e : (i || f.notifyWith(o, [p, 1, 0]), f.resolveWith(o, [p]), !1)
        }, p = f.promise({
            elem: o,
            props: T.extend({}, e),
            opts: T.extend(!0, {specialEasing: {}, easing: T.easing._default}, t),
            originalProperties: e,
            originalOptions: t,
            startTime: O || mt(),
            duration: t.duration,
            tweens: [],
            createTween: function (e, t) {
                t = T.Tween(o, p.opts, e, t, p.opts.specialEasing[e] || p.opts.easing);
                return p.tweens.push(t), t
            },
            stop: function (e) {
                var t = 0, n = e ? p.tweens.length : 0;
                if (r) return this;
                for (r = !0; t < n; t++) p.tweens[t].run(1);
                return e ? (f.notifyWith(o, [p, 1, 0]), f.resolveWith(o, [p, e])) : f.rejectWith(o, [p, e]), this
            }
        }), g = p.props, m = g, v = p.opts.specialEasing;
        for (i in m) if (a = v[s = b(i)], l = m[i], Array.isArray(l) && (a = l[1], l = m[i] = l[0]), i !== s && (m[s] = l, delete m[i]), (c = T.cssHooks[s]) && "expand" in c) for (i in l = c.expand(l), delete m[s], l) i in m || (m[i] = l[i], v[i] = a); else v[s] = a;
        for (; u < d; u++) if (n = j.prefilters[u].call(p, o, g, p.opts)) return y(n.stop) && (T._queueHooks(p.elem, p.opts.queue).stop = n.stop.bind(n)), n;
        return T.map(g, yt, p), y(p.opts.start) && p.opts.start.call(o, p), p.progress(p.opts.progress).done(p.opts.done, p.opts.complete).fail(p.opts.fail).always(p.opts.always), T.fx.timer(T.extend(h, {
            elem: o,
            anim: p,
            queue: p.opts.queue
        })), p
    }

    T.Animation = T.extend(j, {
        tweeners: {
            "*": [function (e, t) {
                var n = this.createTween(e, t);
                return be(n.elem, e, ve.exec(t), n), n
            }]
        }, tweener: function (e, t) {
            for (var n, i = 0, o = (e = y(e) ? (t = e, ["*"]) : e.match(k)).length; i < o; i++) n = e[i], j.tweeners[n] = j.tweeners[n] || [], j.tweeners[n].unshift(t)
        }, prefilters: [function (e, t, n) {
            var i, o, r, s, a, l, c, u = "width" in t || "height" in t, d = this, f = {}, h = e.style,
                p = e.nodeType && me(e), g = _.get(e, "fxshow");
            for (i in n.queue || (null == (s = T._queueHooks(e, "fx")).unqueued && (s.unqueued = 0, a = s.empty.fire, s.empty.fire = function () {
                s.unqueued || a()
            }), s.unqueued++, d.always(function () {
                d.always(function () {
                    s.unqueued--, T.queue(e, "fx").length || s.empty.fire()
                })
            })), t) if (o = t[i], ht.test(o)) {
                if (delete t[i], r = r || "toggle" === o, o === (p ? "hide" : "show")) {
                    if ("show" !== o || !g || void 0 === g[i]) continue;
                    p = !0
                }
                f[i] = g && g[i] || T.style(e, i)
            }
            if ((l = !T.isEmptyObject(t)) || !T.isEmptyObject(f)) for (i in u && 1 === e.nodeType && (n.overflow = [h.overflow, h.overflowX, h.overflowY], null == (c = g && g.display) && (c = _.get(e, "display")), "none" === (u = T.css(e, "display")) && (c ? u = c : (E([e], !0), c = e.style.display || c, u = T.css(e, "display"), E([e]))), ("inline" === u || "inline-block" === u && null != c) && "none" === T.css(e, "float") && (l || (d.done(function () {
                h.display = c
            }), null == c && (u = h.display, c = "none" === u ? "" : u)), h.display = "inline-block")), n.overflow && (h.overflow = "hidden", d.always(function () {
                h.overflow = n.overflow[0], h.overflowX = n.overflow[1], h.overflowY = n.overflow[2]
            })), l = !1, f) l || (g ? "hidden" in g && (p = g.hidden) : g = _.access(e, "fxshow", {display: c}), r && (g.hidden = !p), p && E([e], !0), d.done(function () {
                for (i in p || E([e]), _.remove(e, "fxshow"), f) T.style(e, i, f[i])
            })), l = yt(p ? g[i] : 0, i, d), i in g || (g[i] = l.start, p && (l.end = l.start, l.start = 0))
        }], prefilter: function (e, t) {
            t ? j.prefilters.unshift(e) : j.prefilters.push(e)
        }
    }), T.speed = function (e, t, n) {
        var i = e && "object" == typeof e ? T.extend({}, e) : {
            complete: n || !n && t || y(e) && e,
            duration: e,
            easing: n && t || t && !y(t) && t
        };
        return T.fx.off ? i.duration = 0 : "number" != typeof i.duration && (i.duration in T.fx.speeds ? i.duration = T.fx.speeds[i.duration] : i.duration = T.fx.speeds._default), null != i.queue && !0 !== i.queue || (i.queue = "fx"), i.old = i.complete, i.complete = function () {
            y(i.old) && i.old.call(this), i.queue && T.dequeue(this, i.queue)
        }, i
    }, T.fn.extend({
        fadeTo: function (e, t, n, i) {
            return this.filter(me).css("opacity", 0).show().end().animate({opacity: t}, e, n, i)
        }, animate: function (t, e, n, i) {
            function o() {
                var e = j(this, T.extend({}, t), s);
                (r || _.get(this, "finish")) && e.stop(!0)
            }

            var r = T.isEmptyObject(t), s = T.speed(e, n, i);
            return o.finish = o, r || !1 === s.queue ? this.each(o) : this.queue(s.queue, o)
        }, stop: function (o, e, r) {
            function s(e) {
                var t = e.stop;
                delete e.stop, t(r)
            }

            return "string" != typeof o && (r = e, e = o, o = void 0), e && this.queue(o || "fx", []), this.each(function () {
                var e = !0, t = null != o && o + "queueHooks", n = T.timers, i = _.get(this);
                if (t) i[t] && i[t].stop && s(i[t]); else for (t in i) i[t] && i[t].stop && pt.test(t) && s(i[t]);
                for (t = n.length; t--;) n[t].elem !== this || null != o && n[t].queue !== o || (n[t].anim.stop(r), e = !1, n.splice(t, 1));
                !e && r || T.dequeue(this, o)
            })
        }, finish: function (s) {
            return !1 !== s && (s = s || "fx"), this.each(function () {
                var e, t = _.get(this), n = t[s + "queue"], i = t[s + "queueHooks"], o = T.timers, r = n ? n.length : 0;
                for (t.finish = !0, T.queue(this, s, []), i && i.stop && i.stop.call(this, !0), e = o.length; e--;) o[e].elem === this && o[e].queue === s && (o[e].anim.stop(!0), o.splice(e, 1));
                for (e = 0; e < r; e++) n[e] && n[e].finish && n[e].finish.call(this);
                delete t.finish
            })
        }
    }), T.each(["toggle", "show", "hide"], function (e, i) {
        var o = T.fn[i];
        T.fn[i] = function (e, t, n) {
            return null == e || "boolean" == typeof e ? o.apply(this, arguments) : this.animate(vt(i, !0), e, t, n)
        }
    }), T.each({
        slideDown: vt("show"),
        slideUp: vt("hide"),
        slideToggle: vt("toggle"),
        fadeIn: {opacity: "show"},
        fadeOut: {opacity: "hide"},
        fadeToggle: {opacity: "toggle"}
    }, function (e, i) {
        T.fn[e] = function (e, t, n) {
            return this.animate(i, e, t, n)
        }
    }), T.timers = [], T.fx.tick = function () {
        var e, t = 0, n = T.timers;
        for (O = Date.now(); t < n.length; t++) (e = n[t])() || n[t] !== e || n.splice(t--, 1);
        n.length || T.fx.stop(), O = void 0
    }, T.fx.timer = function (e) {
        T.timers.push(e), T.fx.start()
    }, T.fx.interval = 13, T.fx.start = function () {
        ft || (ft = !0, gt())
    }, T.fx.stop = function () {
        ft = null
    }, T.fx.speeds = {slow: 600, fast: 200, _default: 400}, T.fn.delay = function (i, e) {
        return i = T.fx && T.fx.speeds[i] || i, this.queue(e = e || "fx", function (e, t) {
            var n = w.setTimeout(e, i);
            t.stop = function () {
                w.clearTimeout(n)
            }
        })
    }, N = x.createElement("input"), e = x.createElement("select").appendChild(x.createElement("option")), N.type = "checkbox", m.checkOn = "" !== N.value, m.optSelected = e.selected, (N = x.createElement("input")).value = "t", N.type = "radio", m.radioValue = "t" === N.value;
    var bt, _t = T.expr.attrHandle, wt = (T.fn.extend({
        attr: function (e, t) {
            return d(this, T.attr, e, t, 1 < arguments.length)
        }, removeAttr: function (e) {
            return this.each(function () {
                T.removeAttr(this, e)
            })
        }
    }), T.extend({
        attr: function (e, t, n) {
            var i, o, r = e.nodeType;
            if (3 !== r && 8 !== r && 2 !== r) return void 0 === e.getAttribute ? T.prop(e, t, n) : (1 === r && T.isXMLDoc(e) || (o = T.attrHooks[t.toLowerCase()] || (T.expr.match.bool.test(t) ? bt : void 0)), void 0 !== n ? null === n ? void T.removeAttr(e, t) : o && "set" in o && void 0 !== (i = o.set(e, n, t)) ? i : (e.setAttribute(t, n + ""), n) : !(o && "get" in o && null !== (i = o.get(e, t))) && null == (i = T.find.attr(e, t)) ? void 0 : i)
        }, attrHooks: {
            type: {
                set: function (e, t) {
                    var n;
                    if (!m.radioValue && "radio" === t && l(e, "input")) return n = e.value, e.setAttribute("type", t), n && (e.value = n), t
                }
            }
        }, removeAttr: function (e, t) {
            var n, i = 0, o = t && t.match(k);
            if (o && 1 === e.nodeType) for (; n = o[i++];) e.removeAttribute(n)
        }
    }), bt = {
        set: function (e, t, n) {
            return !1 === t ? T.removeAttr(e, n) : e.setAttribute(n, n), n
        }
    }, T.each(T.expr.match.bool.source.match(/\w+/g), function (e, t) {
        var s = _t[t] || T.find.attr;
        _t[t] = function (e, t, n) {
            var i, o, r = t.toLowerCase();
            return n || (o = _t[r], _t[r] = i, i = null != s(e, t, n) ? r : null, _t[r] = o), i
        }
    }), /^(?:input|select|textarea|button)$/i), xt = /^(?:a|area)$/i;

    function I(e) {
        return (e.match(k) || []).join(" ")
    }

    function P(e) {
        return e.getAttribute && e.getAttribute("class") || ""
    }

    function Tt(e) {
        return Array.isArray(e) ? e : "string" == typeof e && e.match(k) || []
    }

    T.fn.extend({
        prop: function (e, t) {
            return d(this, T.prop, e, t, 1 < arguments.length)
        }, removeProp: function (e) {
            return this.each(function () {
                delete this[T.propFix[e] || e]
            })
        }
    }), T.extend({
        prop: function (e, t, n) {
            var i, o, r = e.nodeType;
            if (3 !== r && 8 !== r && 2 !== r) return 1 === r && T.isXMLDoc(e) || (t = T.propFix[t] || t, o = T.propHooks[t]), void 0 !== n ? o && "set" in o && void 0 !== (i = o.set(e, n, t)) ? i : e[t] = n : o && "get" in o && null !== (i = o.get(e, t)) ? i : e[t]
        }, propHooks: {
            tabIndex: {
                get: function (e) {
                    var t = T.find.attr(e, "tabindex");
                    return t ? parseInt(t, 10) : wt.test(e.nodeName) || xt.test(e.nodeName) && e.href ? 0 : -1
                }
            }
        }, propFix: {for: "htmlFor", class: "className"}
    }), m.optSelected || (T.propHooks.selected = {
        get: function (e) {
            e = e.parentNode;
            return e && e.parentNode && e.parentNode.selectedIndex, null
        }, set: function (e) {
            e = e.parentNode;
            e && (e.selectedIndex, e.parentNode && e.parentNode.selectedIndex)
        }
    }), T.each(["tabIndex", "readOnly", "maxLength", "cellSpacing", "cellPadding", "rowSpan", "colSpan", "useMap", "frameBorder", "contentEditable"], function () {
        T.propFix[this.toLowerCase()] = this
    }), T.fn.extend({
        addClass: function (t) {
            var e, n, i, o, r, s;
            return y(t) ? this.each(function (e) {
                T(this).addClass(t.call(this, e, P(this)))
            }) : (e = Tt(t)).length ? this.each(function () {
                if (i = P(this), n = 1 === this.nodeType && " " + I(i) + " ") {
                    for (r = 0; r < e.length; r++) o = e[r], n.indexOf(" " + o + " ") < 0 && (n += o + " ");
                    s = I(n), i !== s && this.setAttribute("class", s)
                }
            }) : this
        }, removeClass: function (t) {
            var e, n, i, o, r, s;
            return y(t) ? this.each(function (e) {
                T(this).removeClass(t.call(this, e, P(this)))
            }) : arguments.length ? (e = Tt(t)).length ? this.each(function () {
                if (i = P(this), n = 1 === this.nodeType && " " + I(i) + " ") {
                    for (r = 0; r < e.length; r++) for (o = e[r]; -1 < n.indexOf(" " + o + " ");) n = n.replace(" " + o + " ", " ");
                    s = I(n), i !== s && this.setAttribute("class", s)
                }
            }) : this : this.attr("class", "")
        }, toggleClass: function (t, n) {
            var e, i, o, r, s = typeof t, a = "string" == s || Array.isArray(t);
            return y(t) ? this.each(function (e) {
                T(this).toggleClass(t.call(this, e, P(this), n), n)
            }) : "boolean" == typeof n && a ? n ? this.addClass(t) : this.removeClass(t) : (e = Tt(t), this.each(function () {
                if (a) for (r = T(this), o = 0; o < e.length; o++) i = e[o], r.hasClass(i) ? r.removeClass(i) : r.addClass(i); else void 0 !== t && "boolean" != s || ((i = P(this)) && _.set(this, "__className__", i), this.setAttribute && this.setAttribute("class", !i && !1 !== t && _.get(this, "__className__") || ""))
            }))
        }, hasClass: function (e) {
            for (var t, n = 0, i = " " + e + " "; t = this[n++];) if (1 === t.nodeType && -1 < (" " + I(P(t)) + " ").indexOf(i)) return !0;
            return !1
        }
    });

    function kt(e) {
        e.stopPropagation()
    }

    var Ct = /\r/g, At = (T.fn.extend({
        val: function (t) {
            var n, e, i, o = this[0];
            return arguments.length ? (i = y(t), this.each(function (e) {
                1 === this.nodeType && (null == (e = i ? t.call(this, e, T(this).val()) : t) ? e = "" : "number" == typeof e ? e += "" : Array.isArray(e) && (e = T.map(e, function (e) {
                    return null == e ? "" : e + ""
                })), (n = T.valHooks[this.type] || T.valHooks[this.nodeName.toLowerCase()]) && "set" in n && void 0 !== n.set(this, e, "value") || (this.value = e))
            })) : o ? (n = T.valHooks[o.type] || T.valHooks[o.nodeName.toLowerCase()]) && "get" in n && void 0 !== (e = n.get(o, "value")) ? e : "string" == typeof (e = o.value) ? e.replace(Ct, "") : null == e ? "" : e : void 0
        }
    }), T.extend({
        valHooks: {
            option: {
                get: function (e) {
                    var t = T.find.attr(e, "value");
                    return null != t ? t : I(T.text(e))
                }
            }, select: {
                get: function (e) {
                    for (var t, n = e.options, i = e.selectedIndex, o = "select-one" === e.type, r = o ? null : [], s = o ? i + 1 : n.length, a = i < 0 ? s : o ? i : 0; a < s; a++) if (((t = n[a]).selected || a === i) && !t.disabled && (!t.parentNode.disabled || !l(t.parentNode, "optgroup"))) {
                        if (t = T(t).val(), o) return t;
                        r.push(t)
                    }
                    return r
                }, set: function (e, t) {
                    for (var n, i, o = e.options, r = T.makeArray(t), s = o.length; s--;) ((i = o[s]).selected = -1 < T.inArray(T.valHooks.option.get(i), r)) && (n = !0);
                    return n || (e.selectedIndex = -1), r
                }
            }
        }
    }), T.each(["radio", "checkbox"], function () {
        T.valHooks[this] = {
            set: function (e, t) {
                if (Array.isArray(t)) return e.checked = -1 < T.inArray(T(e).val(), t)
            }
        }, m.checkOn || (T.valHooks[this].get = function (e) {
            return null === e.getAttribute("value") ? "on" : e.value
        })
    }), m.focusin = "onfocusin" in w, /^(?:focusinfocus|focusoutblur)$/), Et = (T.extend(T.event, {
        trigger: function (e, t, n, i) {
            var o, r, s, a, l, c, u, d = [n || x], f = z.call(e, "type") ? e.type : e,
                h = z.call(e, "namespace") ? e.namespace.split(".") : [], p = u = r = n = n || x;
            if (3 !== n.nodeType && 8 !== n.nodeType && !At.test(f + T.event.triggered) && (-1 < f.indexOf(".") && (f = (h = f.split(".")).shift(), h.sort()), a = f.indexOf(":") < 0 && "on" + f, (e = e[T.expando] ? e : new T.Event(f, "object" == typeof e && e)).isTrigger = i ? 2 : 3, e.namespace = h.join("."), e.rnamespace = e.namespace ? new RegExp("(^|\\.)" + h.join("\\.(?:.*\\.|)") + "(\\.|$)") : null, e.result = void 0, e.target || (e.target = n), t = null == t ? [e] : T.makeArray(t, [e]), c = T.event.special[f] || {}, i || !c.trigger || !1 !== c.trigger.apply(n, t))) {
                if (!i && !c.noBubble && !g(n)) {
                    for (s = c.delegateType || f, At.test(s + f) || (p = p.parentNode); p; p = p.parentNode) d.push(p), r = p;
                    r === (n.ownerDocument || x) && d.push(r.defaultView || r.parentWindow || w)
                }
                for (o = 0; (p = d[o++]) && !e.isPropagationStopped();) u = p, e.type = 1 < o ? s : c.bindType || f, (l = (_.get(p, "events") || Object.create(null))[e.type] && _.get(p, "handle")) && l.apply(p, t), (l = a && p[a]) && l.apply && v(p) && (e.result = l.apply(p, t), !1 === e.result && e.preventDefault());
                return e.type = f, i || e.isDefaultPrevented() || c._default && !1 !== c._default.apply(d.pop(), t) || !v(n) || a && y(n[f]) && !g(n) && ((r = n[a]) && (n[a] = null), T.event.triggered = f, e.isPropagationStopped() && u.addEventListener(f, kt), n[f](), e.isPropagationStopped() && u.removeEventListener(f, kt), T.event.triggered = void 0, r && (n[a] = r)), e.result
            }
        }, simulate: function (e, t, n) {
            n = T.extend(new T.Event, n, {type: e, isSimulated: !0});
            T.event.trigger(n, null, t)
        }
    }), T.fn.extend({
        trigger: function (e, t) {
            return this.each(function () {
                T.event.trigger(e, t, this)
            })
        }, triggerHandler: function (e, t) {
            var n = this[0];
            if (n) return T.event.trigger(e, t, n, !0)
        }
    }), m.focusin || T.each({focus: "focusin", blur: "focusout"}, function (n, i) {
        function o(e) {
            T.event.simulate(i, e.target, T.event.fix(e))
        }

        T.event.special[i] = {
            setup: function () {
                var e = this.ownerDocument || this.document || this, t = _.access(e, i);
                t || e.addEventListener(n, o, !0), _.access(e, i, (t || 0) + 1)
            }, teardown: function () {
                var e = this.ownerDocument || this.document || this, t = _.access(e, i) - 1;
                t ? _.access(e, i, t) : (e.removeEventListener(n, o, !0), _.remove(e, i))
            }
        }
    }), w.location), St = {guid: Date.now()}, Dt = /\?/, Lt = (T.parseXML = function (e) {
        var t, n;
        if (!e || "string" != typeof e) return null;
        try {
            t = (new w.DOMParser).parseFromString(e, "text/xml")
        } catch (e) {
        }
        return n = t && t.getElementsByTagName("parsererror")[0], t && !n || T.error("Invalid XML: " + (n ? T.map(n.childNodes, function (e) {
            return e.textContent
        }).join("\n") : e)), t
    }, /\[\]$/), Ot = /\r?\n/g, Nt = /^(?:submit|button|image|reset|file)$/i, jt = /^(?:input|select|textarea|keygen)/i;
    T.param = function (e, t) {
        function n(e, t) {
            t = y(t) ? t() : t, o[o.length] = encodeURIComponent(e) + "=" + encodeURIComponent(null == t ? "" : t)
        }

        var i, o = [];
        if (null == e) return "";
        if (Array.isArray(e) || e.jquery && !T.isPlainObject(e)) T.each(e, function () {
            n(this.name, this.value)
        }); else for (i in e) !function n(i, e, o, r) {
            if (Array.isArray(e)) T.each(e, function (e, t) {
                o || Lt.test(i) ? r(i, t) : n(i + "[" + ("object" == typeof t && null != t ? e : "") + "]", t, o, r)
            }); else if (o || "object" !== p(e)) r(i, e); else for (var t in e) n(i + "[" + t + "]", e[t], o, r)
        }(i, e[i], t, n);
        return o.join("&")
    }, T.fn.extend({
        serialize: function () {
            return T.param(this.serializeArray())
        }, serializeArray: function () {
            return this.map(function () {
                var e = T.prop(this, "elements");
                return e ? T.makeArray(e) : this
            }).filter(function () {
                var e = this.type;
                return this.name && !T(this).is(":disabled") && jt.test(this.nodeName) && !Nt.test(e) && (this.checked || !we.test(e))
            }).map(function (e, t) {
                var n = T(this).val();
                return null == n ? null : Array.isArray(n) ? T.map(n, function (e) {
                    return {name: t.name, value: e.replace(Ot, "\r\n")}
                }) : {name: t.name, value: n.replace(Ot, "\r\n")}
            }).get()
        }
    });
    var It = /%20/g, Pt = /#.*$/, Mt = /([?&])_=[^&]*/, Ht = /^(.*?):[ \t]*([^\r\n]*)$/gm, qt = /^(?:GET|HEAD)$/,
        Rt = /^\/\//, Bt = {}, Ft = {}, Wt = "*/".concat("*"), zt = x.createElement("a");

    function $t(r) {
        return function (e, t) {
            "string" != typeof e && (t = e, e = "*");
            var n, i = 0, o = e.toLowerCase().match(k) || [];
            if (y(t)) for (; n = o[i++];) "+" === n[0] ? (n = n.slice(1) || "*", (r[n] = r[n] || []).unshift(t)) : (r[n] = r[n] || []).push(t)
        }
    }

    function Vt(t, i, o, r) {
        var s = {}, a = t === Ft;

        function l(e) {
            var n;
            return s[e] = !0, T.each(t[e] || [], function (e, t) {
                t = t(i, o, r);
                return "string" != typeof t || a || s[t] ? a ? !(n = t) : void 0 : (i.dataTypes.unshift(t), l(t), !1)
            }), n
        }

        return l(i.dataTypes[0]) || !s["*"] && l("*")
    }

    function Xt(e, t) {
        var n, i, o = T.ajaxSettings.flatOptions || {};
        for (n in t) void 0 !== t[n] && ((o[n] ? e : i = i || {})[n] = t[n]);
        return i && T.extend(!0, e, i), e
    }

    zt.href = Et.href, T.extend({
        active: 0,
        lastModified: {},
        etag: {},
        ajaxSettings: {
            url: Et.href,
            type: "GET",
            isLocal: /^(?:about|app|app-storage|.+-extension|file|res|widget):$/.test(Et.protocol),
            global: !0,
            processData: !0,
            async: !0,
            contentType: "application/x-www-form-urlencoded; charset=UTF-8",
            accepts: {
                "*": Wt,
                text: "text/plain",
                html: "text/html",
                xml: "application/xml, text/xml",
                json: "application/json, text/javascript"
            },
            contents: {xml: /\bxml\b/, html: /\bhtml/, json: /\bjson\b/},
            responseFields: {xml: "responseXML", text: "responseText", json: "responseJSON"},
            converters: {"* text": String, "text html": !0, "text json": JSON.parse, "text xml": T.parseXML},
            flatOptions: {url: !0, context: !0}
        },
        ajaxSetup: function (e, t) {
            return t ? Xt(Xt(e, T.ajaxSettings), t) : Xt(T.ajaxSettings, e)
        },
        ajaxPrefilter: $t(Bt),
        ajaxTransport: $t(Ft),
        ajax: function (e, t) {
            "object" == typeof e && (t = e, e = void 0);
            var l, c, u, n, d, f, h, i, p = T.ajaxSetup({}, t = t || {}), g = p.context || p,
                m = p.context && (g.nodeType || g.jquery) ? T(g) : T.event, v = T.Deferred(),
                y = T.Callbacks("once memory"), b = p.statusCode || {}, o = {}, r = {}, s = "canceled", _ = {
                    readyState: 0, getResponseHeader: function (e) {
                        var t;
                        if (f) {
                            if (!n) for (n = {}; t = Ht.exec(u);) n[t[1].toLowerCase() + " "] = (n[t[1].toLowerCase() + " "] || []).concat(t[2]);
                            t = n[e.toLowerCase() + " "]
                        }
                        return null == t ? null : t.join(", ")
                    }, getAllResponseHeaders: function () {
                        return f ? u : null
                    }, setRequestHeader: function (e, t) {
                        return null == f && (e = r[e.toLowerCase()] = r[e.toLowerCase()] || e, o[e] = t), this
                    }, overrideMimeType: function (e) {
                        return null == f && (p.mimeType = e), this
                    }, statusCode: function (e) {
                        if (e) if (f) _.always(e[_.status]); else for (var t in e) b[t] = [b[t], e[t]];
                        return this
                    }, abort: function (e) {
                        e = e || s;
                        return l && l.abort(e), a(0, e), this
                    }
                };
            if (v.promise(_), p.url = ((e || p.url || Et.href) + "").replace(Rt, Et.protocol + "//"), p.type = t.method || t.type || p.method || p.type, p.dataTypes = (p.dataType || "*").toLowerCase().match(k) || [""], null == p.crossDomain) {
                e = x.createElement("a");
                try {
                    e.href = p.url, e.href = e.href, p.crossDomain = zt.protocol + "//" + zt.host != e.protocol + "//" + e.host
                } catch (e) {
                    p.crossDomain = !0
                }
            }
            if (p.data && p.processData && "string" != typeof p.data && (p.data = T.param(p.data, p.traditional)), Vt(Bt, p, t, _), f) return _;
            for (i in (h = T.event && p.global) && 0 == T.active++ && T.event.trigger("ajaxStart"), p.type = p.type.toUpperCase(), p.hasContent = !qt.test(p.type), c = p.url.replace(Pt, ""), p.hasContent ? p.data && p.processData && 0 === (p.contentType || "").indexOf("application/x-www-form-urlencoded") && (p.data = p.data.replace(It, "+")) : (e = p.url.slice(c.length), p.data && (p.processData || "string" == typeof p.data) && (c += (Dt.test(c) ? "&" : "?") + p.data, delete p.data), !1 === p.cache && (c = c.replace(Mt, "$1"), e = (Dt.test(c) ? "&" : "?") + "_=" + St.guid++ + e), p.url = c + e), p.ifModified && (T.lastModified[c] && _.setRequestHeader("If-Modified-Since", T.lastModified[c]), T.etag[c] && _.setRequestHeader("If-None-Match", T.etag[c])), (p.data && p.hasContent && !1 !== p.contentType || t.contentType) && _.setRequestHeader("Content-Type", p.contentType), _.setRequestHeader("Accept", p.dataTypes[0] && p.accepts[p.dataTypes[0]] ? p.accepts[p.dataTypes[0]] + ("*" !== p.dataTypes[0] ? ", " + Wt + "; q=0.01" : "") : p.accepts["*"]), p.headers) _.setRequestHeader(i, p.headers[i]);
            if (p.beforeSend && (!1 === p.beforeSend.call(g, _, p) || f)) return _.abort();
            if (s = "abort", y.add(p.complete), _.done(p.success), _.fail(p.error), l = Vt(Ft, p, t, _)) {
                if (_.readyState = 1, h && m.trigger("ajaxSend", [_, p]), f) return _;
                p.async && 0 < p.timeout && (d = w.setTimeout(function () {
                    _.abort("timeout")
                }, p.timeout));
                try {
                    f = !1, l.send(o, a)
                } catch (e) {
                    if (f) throw e;
                    a(-1, e)
                }
            } else a(-1, "No Transport");

            function a(e, t, n, i) {
                var o, r, s, a = t;
                f || (f = !0, d && w.clearTimeout(d), l = void 0, u = i || "", _.readyState = 0 < e ? 4 : 0, i = 200 <= e && e < 300 || 304 === e, n && (s = function (e, t, n) {
                    for (var i, o, r, s, a = e.contents, l = e.dataTypes; "*" === l[0];) l.shift(), void 0 === i && (i = e.mimeType || t.getResponseHeader("Content-Type"));
                    if (i) for (o in a) if (a[o] && a[o].test(i)) {
                        l.unshift(o);
                        break
                    }
                    if (l[0] in n) r = l[0]; else {
                        for (o in n) {
                            if (!l[0] || e.converters[o + " " + l[0]]) {
                                r = o;
                                break
                            }
                            s = s || o
                        }
                        r = r || s
                    }
                    if (r) return r !== l[0] && l.unshift(r), n[r]
                }(p, _, n)), !i && -1 < T.inArray("script", p.dataTypes) && T.inArray("json", p.dataTypes) < 0 && (p.converters["text script"] = function () {
                }), s = function (e, t, n, i) {
                    var o, r, s, a, l, c = {}, u = e.dataTypes.slice();
                    if (u[1]) for (s in e.converters) c[s.toLowerCase()] = e.converters[s];
                    for (r = u.shift(); r;) if (e.responseFields[r] && (n[e.responseFields[r]] = t), !l && i && e.dataFilter && (t = e.dataFilter(t, e.dataType)), l = r, r = u.shift()) if ("*" === r) r = l; else if ("*" !== l && l !== r) {
                        if (!(s = c[l + " " + r] || c["* " + r])) for (o in c) if ((a = o.split(" "))[1] === r && (s = c[l + " " + a[0]] || c["* " + a[0]])) {
                            !0 === s ? s = c[o] : !0 !== c[o] && (r = a[0], u.unshift(a[1]));
                            break
                        }
                        if (!0 !== s) if (s && e.throws) t = s(t); else try {
                            t = s(t)
                        } catch (e) {
                            return {state: "parsererror", error: s ? e : "No conversion from " + l + " to " + r}
                        }
                    }
                    return {state: "success", data: t}
                }(p, s, _, i), i ? (p.ifModified && ((n = _.getResponseHeader("Last-Modified")) && (T.lastModified[c] = n), (n = _.getResponseHeader("etag")) && (T.etag[c] = n)), 204 === e || "HEAD" === p.type ? a = "nocontent" : 304 === e ? a = "notmodified" : (a = s.state, o = s.data, i = !(r = s.error))) : (r = a, !e && a || (a = "error", e < 0 && (e = 0))), _.status = e, _.statusText = (t || a) + "", i ? v.resolveWith(g, [o, a, _]) : v.rejectWith(g, [_, a, r]), _.statusCode(b), b = void 0, h && m.trigger(i ? "ajaxSuccess" : "ajaxError", [_, p, i ? o : r]), y.fireWith(g, [_, a]), h && (m.trigger("ajaxComplete", [_, p]), --T.active || T.event.trigger("ajaxStop")))
            }

            return _
        },
        getJSON: function (e, t, n) {
            return T.get(e, t, n, "json")
        },
        getScript: function (e, t) {
            return T.get(e, void 0, t, "script")
        }
    }), T.each(["get", "post"], function (e, o) {
        T[o] = function (e, t, n, i) {
            return y(t) && (i = i || n, n = t, t = void 0), T.ajax(T.extend({
                url: e,
                type: o,
                dataType: i,
                data: t,
                success: n
            }, T.isPlainObject(e) && e))
        }
    }), T.ajaxPrefilter(function (e) {
        for (var t in e.headers) "content-type" === t.toLowerCase() && (e.contentType = e.headers[t] || "")
    }), T._evalUrl = function (e, t, n) {
        return T.ajax({
            url: e,
            type: "GET",
            dataType: "script",
            cache: !0,
            async: !1,
            global: !1,
            converters: {
                "text script": function () {
                }
            },
            dataFilter: function (e) {
                T.globalEval(e, t, n)
            }
        })
    }, T.fn.extend({
        wrapAll: function (e) {
            return this[0] && (y(e) && (e = e.call(this[0])), e = T(e, this[0].ownerDocument).eq(0).clone(!0), this[0].parentNode && e.insertBefore(this[0]), e.map(function () {
                for (var e = this; e.firstElementChild;) e = e.firstElementChild;
                return e
            }).append(this)), this
        }, wrapInner: function (n) {
            return y(n) ? this.each(function (e) {
                T(this).wrapInner(n.call(this, e))
            }) : this.each(function () {
                var e = T(this), t = e.contents();
                t.length ? t.wrapAll(n) : e.append(n)
            })
        }, wrap: function (t) {
            var n = y(t);
            return this.each(function (e) {
                T(this).wrapAll(n ? t.call(this, e) : t)
            })
        }, unwrap: function (e) {
            return this.parent(e).not("body").each(function () {
                T(this).replaceWith(this.childNodes)
            }), this
        }
    }), T.expr.pseudos.hidden = function (e) {
        return !T.expr.pseudos.visible(e)
    }, T.expr.pseudos.visible = function (e) {
        return !!(e.offsetWidth || e.offsetHeight || e.getClientRects().length)
    }, T.ajaxSettings.xhr = function () {
        try {
            return new w.XMLHttpRequest
        } catch (e) {
        }
    };
    var Ut = {0: 200, 1223: 204}, Yt = T.ajaxSettings.xhr(),
        Qt = (m.cors = !!Yt && "withCredentials" in Yt, m.ajax = Yt = !!Yt, T.ajaxTransport(function (o) {
            var r, s;
            if (m.cors || Yt && !o.crossDomain) return {
                send: function (e, t) {
                    var n, i = o.xhr();
                    if (i.open(o.type, o.url, o.async, o.username, o.password), o.xhrFields) for (n in o.xhrFields) i[n] = o.xhrFields[n];
                    for (n in o.mimeType && i.overrideMimeType && i.overrideMimeType(o.mimeType), o.crossDomain || e["X-Requested-With"] || (e["X-Requested-With"] = "XMLHttpRequest"), e) i.setRequestHeader(n, e[n]);
                    r = function (e) {
                        return function () {
                            r && (r = s = i.onload = i.onerror = i.onabort = i.ontimeout = i.onreadystatechange = null, "abort" === e ? i.abort() : "error" === e ? "number" != typeof i.status ? t(0, "error") : t(i.status, i.statusText) : t(Ut[i.status] || i.status, i.statusText, "text" !== (i.responseType || "text") || "string" != typeof i.responseText ? {binary: i.response} : {text: i.responseText}, i.getAllResponseHeaders()))
                        }
                    }, i.onload = r(), s = i.onerror = i.ontimeout = r("error"), void 0 !== i.onabort ? i.onabort = s : i.onreadystatechange = function () {
                        4 === i.readyState && w.setTimeout(function () {
                            r && s()
                        })
                    }, r = r("abort");
                    try {
                        i.send(o.hasContent && o.data || null)
                    } catch (e) {
                        if (r) throw e
                    }
                }, abort: function () {
                    r && r()
                }
            }
        }), T.ajaxPrefilter(function (e) {
            e.crossDomain && (e.contents.script = !1)
        }), T.ajaxSetup({
            accepts: {script: "text/javascript, application/javascript, application/ecmascript, application/x-ecmascript"},
            contents: {script: /\b(?:java|ecma)script\b/},
            converters: {
                "text script": function (e) {
                    return T.globalEval(e), e
                }
            }
        }), T.ajaxPrefilter("script", function (e) {
            void 0 === e.cache && (e.cache = !1), e.crossDomain && (e.type = "GET")
        }), T.ajaxTransport("script", function (n) {
            var i, o;
            if (n.crossDomain || n.scriptAttrs) return {
                send: function (e, t) {
                    i = T("<script>").attr(n.scriptAttrs || {}).prop({
                        charset: n.scriptCharset,
                        src: n.url
                    }).on("load error", o = function (e) {
                        i.remove(), o = null, e && t("error" === e.type ? 404 : 200, e.type)
                    }), x.head.appendChild(i[0])
                }, abort: function () {
                    o && o()
                }
            }
        }), []), Gt = /(=)\?(?=&|$)|\?\?/, Kt = (T.ajaxSetup({
            jsonp: "callback", jsonpCallback: function () {
                var e = Qt.pop() || T.expando + "_" + St.guid++;
                return this[e] = !0, e
            }
        }), T.ajaxPrefilter("json jsonp", function (e, t, n) {
            var i, o, r,
                s = !1 !== e.jsonp && (Gt.test(e.url) ? "url" : "string" == typeof e.data && 0 === (e.contentType || "").indexOf("application/x-www-form-urlencoded") && Gt.test(e.data) && "data");
            if (s || "jsonp" === e.dataTypes[0]) return i = e.jsonpCallback = y(e.jsonpCallback) ? e.jsonpCallback() : e.jsonpCallback, s ? e[s] = e[s].replace(Gt, "$1" + i) : !1 !== e.jsonp && (e.url += (Dt.test(e.url) ? "&" : "?") + e.jsonp + "=" + i), e.converters["script json"] = function () {
                return r || T.error(i + " was not called"), r[0]
            }, e.dataTypes[0] = "json", o = w[i], w[i] = function () {
                r = arguments
            }, n.always(function () {
                void 0 === o ? T(w).removeProp(i) : w[i] = o, e[i] && (e.jsonpCallback = t.jsonpCallback, Qt.push(i)), r && y(o) && o(r[0]), r = o = void 0
            }), "script"
        }), m.createHTMLDocument = ((r = x.implementation.createHTMLDocument("").body).innerHTML = "<form></form><form></form>", 2 === r.childNodes.length), T.parseHTML = function (e, t, n) {
            return "string" != typeof e ? [] : ("boolean" == typeof t && (n = t, t = !1), t || (m.createHTMLDocument ? ((i = (t = x.implementation.createHTMLDocument("")).createElement("base")).href = x.location.href, t.head.appendChild(i)) : t = x), i = !n && [], (n = K.exec(e)) ? [t.createElement(n[1])] : (n = Ae([e], t, i), i && i.length && T(i).remove(), T.merge([], n.childNodes)));
            var i
        }, T.fn.load = function (e, t, n) {
            var i, o, r, s = this, a = e.indexOf(" ");
            return -1 < a && (i = I(e.slice(a)), e = e.slice(0, a)), y(t) ? (n = t, t = void 0) : t && "object" == typeof t && (o = "POST"), 0 < s.length && T.ajax({
                url: e,
                type: o || "GET",
                dataType: "html",
                data: t
            }).done(function (e) {
                r = arguments, s.html(i ? T("<div>").append(T.parseHTML(e)).find(i) : e)
            }).always(n && function (e, t) {
                s.each(function () {
                    n.apply(this, r || [e.responseText, t, e])
                })
            }), this
        }, T.expr.pseudos.animated = function (t) {
            return T.grep(T.timers, function (e) {
                return t === e.elem
            }).length
        }, T.offset = {
            setOffset: function (e, t, n) {
                var i, o, r, s, a = T.css(e, "position"), l = T(e), c = {};
                "static" === a && (e.style.position = "relative"), r = l.offset(), i = T.css(e, "top"), s = T.css(e, "left"), a = ("absolute" === a || "fixed" === a) && -1 < (i + s).indexOf("auto") ? (o = (a = l.position()).top, a.left) : (o = parseFloat(i) || 0, parseFloat(s) || 0), null != (t = y(t) ? t.call(e, n, T.extend({}, r)) : t).top && (c.top = t.top - r.top + o), null != t.left && (c.left = t.left - r.left + a), "using" in t ? t.using.call(e, c) : l.css(c)
            }
        }, T.fn.extend({
            offset: function (t) {
                if (arguments.length) return void 0 === t ? this : this.each(function (e) {
                    T.offset.setOffset(this, t, e)
                });
                var e, n = this[0];
                return n ? n.getClientRects().length ? (e = n.getBoundingClientRect(), n = n.ownerDocument.defaultView, {
                    top: e.top + n.pageYOffset,
                    left: e.left + n.pageXOffset
                }) : {top: 0, left: 0} : void 0
            }, position: function () {
                if (this[0]) {
                    var e, t, n, i = this[0], o = {top: 0, left: 0};
                    if ("fixed" === T.css(i, "position")) t = i.getBoundingClientRect(); else {
                        for (t = this.offset(), n = i.ownerDocument, e = i.offsetParent || n.documentElement; e && (e === n.body || e === n.documentElement) && "static" === T.css(e, "position");) e = e.parentNode;
                        e && e !== i && 1 === e.nodeType && ((o = T(e).offset()).top += T.css(e, "borderTopWidth", !0), o.left += T.css(e, "borderLeftWidth", !0))
                    }
                    return {
                        top: t.top - o.top - T.css(i, "marginTop", !0),
                        left: t.left - o.left - T.css(i, "marginLeft", !0)
                    }
                }
            }, offsetParent: function () {
                return this.map(function () {
                    for (var e = this.offsetParent; e && "static" === T.css(e, "position");) e = e.offsetParent;
                    return e || C
                })
            }
        }), T.each({scrollLeft: "pageXOffset", scrollTop: "pageYOffset"}, function (t, o) {
            var r = "pageYOffset" === o;
            T.fn[t] = function (e) {
                return d(this, function (e, t, n) {
                    var i;
                    if (g(e) ? i = e : 9 === e.nodeType && (i = e.defaultView), void 0 === n) return i ? i[o] : e[t];
                    i ? i.scrollTo(r ? i.pageXOffset : n, r ? n : i.pageYOffset) : e[t] = n
                }, t, e, arguments.length)
            }
        }), T.each(["top", "left"], function (e, n) {
            T.cssHooks[n] = tt(m.pixelPosition, function (e, t) {
                if (t) return t = et(e, n), Ye.test(t) ? T(e).position()[n] + "px" : t
            })
        }), T.each({Height: "height", Width: "width"}, function (s, a) {
            T.each({padding: "inner" + s, content: a, "": "outer" + s}, function (i, r) {
                T.fn[r] = function (e, t) {
                    var n = arguments.length && (i || "boolean" != typeof e),
                        o = i || (!0 === e || !0 === t ? "margin" : "border");
                    return d(this, function (e, t, n) {
                        var i;
                        return g(e) ? 0 === r.indexOf("outer") ? e["inner" + s] : e.document.documentElement["client" + s] : 9 === e.nodeType ? (i = e.documentElement, Math.max(e.body["scroll" + s], i["scroll" + s], e.body["offset" + s], i["offset" + s], i["client" + s])) : void 0 === n ? T.css(e, t, o) : T.style(e, t, n, o)
                    }, a, n ? e : void 0, n)
                }
            })
        }), T.each(["ajaxStart", "ajaxStop", "ajaxComplete", "ajaxError", "ajaxSuccess", "ajaxSend"], function (e, t) {
            T.fn[t] = function (e) {
                return this.on(t, e)
            }
        }), T.fn.extend({
            bind: function (e, t, n) {
                return this.on(e, null, t, n)
            }, unbind: function (e, t) {
                return this.off(e, null, t)
            }, delegate: function (e, t, n, i) {
                return this.on(t, e, n, i)
            }, undelegate: function (e, t, n) {
                return 1 === arguments.length ? this.off(e, "**") : this.off(t, e || "**", n)
            }, hover: function (e, t) {
                return this.mouseenter(e).mouseleave(t || e)
            }
        }), T.each("blur focus focusin focusout resize scroll click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select submit keydown keypress keyup contextmenu".split(" "), function (e, n) {
            T.fn[n] = function (e, t) {
                return 0 < arguments.length ? this.on(n, null, e, t) : this.trigger(n)
            }
        }), /^[\s\uFEFF\xA0]+|([^\s\uFEFF\xA0])[\s\uFEFF\xA0]+$/g), Jt = (T.proxy = function (e, t) {
            var n, i;
            if ("string" == typeof t && (i = e[t], t = e, e = i), y(e)) return n = a.call(arguments, 2), (i = function () {
                return e.apply(t || this, n.concat(a.call(arguments)))
            }).guid = e.guid = e.guid || T.guid++, i
        }, T.holdReady = function (e) {
            e ? T.readyWait++ : T.ready(!0)
        }, T.isArray = Array.isArray, T.parseJSON = JSON.parse, T.nodeName = l, T.isFunction = y, T.isWindow = g, T.camelCase = b, T.type = p, T.now = Date.now, T.isNumeric = function (e) {
            var t = T.type(e);
            return ("number" === t || "string" === t) && !isNaN(e - parseFloat(e))
        }, T.trim = function (e) {
            return null == e ? "" : (e + "").replace(Kt, "$1")
        }, "function" == typeof define && define.amd && define("jquery", [], function () {
            return T
        }), w.jQuery), Zt = w.$;
    return T.noConflict = function (e) {
        return w.$ === T && (w.$ = Zt), e && w.jQuery === T && (w.jQuery = Jt), T
    }, void 0 === M && (w.jQuery = w.$ = T), T
}), function (e, t) {
    "object" == typeof exports && "undefined" != typeof module ? module.exports = t() : "function" == typeof define && define.amd ? define(t) : (e = "undefined" != typeof globalThis ? globalThis : e || self).bootstrap = t()
}(this, function () {
    "use strict";
    const M = 1e3, H = "transitionend", q = t => {
            let n = t.getAttribute("data-bs-target");
            if (!n || "#" === n) {
                let e = t.getAttribute("href");
                if (!e || !e.includes("#") && !e.startsWith(".")) return null;
                e.includes("#") && !e.startsWith("#") && (e = "#" + e.split("#")[1]), n = e && "#" !== e ? e.trim() : null
            }
            return n
        }, R = e => {
            e = q(e);
            return e && document.querySelector(e) ? e : null
        }, r = e => {
            e = q(e);
            return e ? document.querySelector(e) : null
        }, B = e => {
            e.dispatchEvent(new Event(H))
        }, s = e => !(!e || "object" != typeof e) && void 0 !== (e = void 0 !== e.jquery ? e[0] : e).nodeType,
        o = e => s(e) ? e.jquery ? e[0] : e : "string" == typeof e && 0 < e.length ? document.querySelector(e) : null,
        i = e => {
            if (!s(e) || 0 === e.getClientRects().length) return !1;
            var t = "visible" === getComputedStyle(e).getPropertyValue("visibility"),
                n = e.closest("details:not([open])");
            if (!n) return t;
            if (n !== e) {
                e = e.closest("summary");
                if (e && e.parentNode !== n) return !1;
                if (null === e) return !1
            }
            return t
        },
        a = e => !e || e.nodeType !== Node.ELEMENT_NODE || (!!e.classList.contains("disabled") || (void 0 !== e.disabled ? e.disabled : e.hasAttribute("disabled") && "false" !== e.getAttribute("disabled"))),
        F = e => {
            return document.documentElement.attachShadow ? "function" == typeof e.getRootNode ? (t = e.getRootNode()) instanceof ShadowRoot ? t : null : e instanceof ShadowRoot ? e : e.parentNode ? F(e.parentNode) : null : null;
            var t
        }, W = () => {
        }, z = e => {
            e.offsetHeight
        }, $ = () => window.jQuery && !document.body.hasAttribute("data-bs-no-jquery") ? window.jQuery : null, V = [],
        l = () => "rtl" === document.documentElement.dir;
    var e = i => {
        var e;
        e = () => {
            const e = $();
            if (e) {
                const t = i.NAME, n = e.fn[t];
                e.fn[t] = i.jQueryInterface, e.fn[t].Constructor = i, e.fn[t].noConflict = () => (e.fn[t] = n, i.jQueryInterface)
            }
        }, "loading" === document.readyState ? (V.length || document.addEventListener("DOMContentLoaded", () => {
            for (const e of V) e()
        }), V.push(e)) : e()
    };
    const c = e => {
        "function" == typeof e && e()
    }, X = (n, i, e = !0) => {
        if (e) {
            e = (e => {
                if (!e) return 0;
                let {transitionDuration: t, transitionDelay: n} = window.getComputedStyle(e);
                var e = Number.parseFloat(t), i = Number.parseFloat(n);
                return e || i ? (t = t.split(",")[0], n = n.split(",")[0], (Number.parseFloat(t) + Number.parseFloat(n)) * M) : 0
            })(i) + 5;
            let t = !1;
            const o = ({target: e}) => {
                e === i && (t = !0, i.removeEventListener(H, o), c(n))
            };
            i.addEventListener(H, o), setTimeout(() => {
                t || B(i)
            }, e)
        } else c(n)
    }, U = (e, t, n, i) => {
        var o = e.length;
        let r = e.indexOf(t);
        return -1 === r ? !n && i ? e[o - 1] : e[0] : (r += n ? 1 : -1, i && (r = (r + o) % o), e[Math.max(0, Math.min(r, o - 1))])
    }, Y = /[^.]*(?=\..*)\.|.*/, Q = /\..*/, G = /::\d+$/, K = {};
    let J = 1;
    const Z = {mouseenter: "mouseover", mouseleave: "mouseout"},
        ee = new Set(["click", "dblclick", "mouseup", "mousedown", "contextmenu", "mousewheel", "DOMMouseScroll", "mouseover", "mouseout", "mousemove", "selectstart", "selectend", "keydown", "keypress", "keyup", "orientationchange", "touchstart", "touchmove", "touchend", "touchcancel", "pointerdown", "pointermove", "pointerup", "pointerleave", "pointercancel", "gesturestart", "gesturechange", "gestureend", "focus", "blur", "change", "reset", "select", "submit", "focusin", "focusout", "load", "unload", "beforeunload", "resize", "move", "DOMContentLoaded", "readystatechange", "error", "abort", "scroll"]);

    function te(e, t) {
        return t && t + "::" + J++ || e.uidEvent || J++
    }

    function ne(e) {
        var t = te(e);
        return e.uidEvent = t, K[t] = K[t] || {}, K[t]
    }

    function ie(e, t, n = null) {
        return Object.values(e).find(e => e.callable === t && e.delegationSelector === n)
    }

    function oe(e, t, n) {
        var i = "string" == typeof t, t = !i && t || n;
        let o = ae(e);
        return [i, t, o = ee.has(o) ? o : e]
    }

    function re(i, o, r, s, a) {
        if ("string" == typeof o && i) {
            let [e, t, n] = oe(o, r, s);
            var l;
            o in Z && (t = (l = t, function (e) {
                if (!e.relatedTarget || e.relatedTarget !== e.delegateTarget && !e.delegateTarget.contains(e.relatedTarget)) return l.call(this, e)
            }));
            const p = ne(i), g = p[n] || (p[n] = {}), m = ie(g, t, e ? r : null);
            if (m) m.oneOff = m.oneOff && a; else {
                var c, u, d, f, h, s = te(t, o.replace(Y, ""));
                const v = e ? (d = i, f = r, h = t, function t(n) {
                    var i = d.querySelectorAll(f);
                    for (let e = n["target"]; e && e !== this; e = e.parentNode) for (const o of i) if (o === e) return le(n, {delegateTarget: e}), t.oneOff && y.off(d, n.type, f, h), h.apply(e, [n])
                }) : (c = i, u = t, function e(t) {
                    return le(t, {delegateTarget: c}), e.oneOff && y.off(c, t.type, u), u.apply(c, [t])
                });
                v.delegationSelector = e ? r : null, v.callable = t, v.oneOff = a, v.uidEvent = s, g[s] = v, i.addEventListener(n, v, e)
            }
        }
    }

    function se(e, t, n, i, o) {
        i = ie(t[n], i, o);
        i && (e.removeEventListener(n, i, Boolean(o)), delete t[n][i.uidEvent])
    }

    function ae(e) {
        return e = e.replace(Q, ""), Z[e] || e
    }

    const y = {
        on(e, t, n, i) {
            re(e, t, n, i, !1)
        }, one(e, t, n, i) {
            re(e, t, n, i, !0)
        }, off(e, t, n, i) {
            if ("string" == typeof t && e) {
                var [i, o, r] = oe(t, n, i), s = r !== t, a = ne(e), l = a[r] || {}, c = t.startsWith(".");
                if (void 0 !== o) return Object.keys(l).length ? void se(e, a, r, o, i ? n : null) : void 0;
                if (c) for (const v of Object.keys(a)) {
                    u = g = p = h = f = d = void 0;
                    var u, d = e, f = a, h = v, p = t.slice(1), g = f[h] || {};
                    for (const y of Object.keys(g)) y.includes(p) && se(d, f, h, (u = g[y]).callable, u.delegationSelector)
                }
                for (const b of Object.keys(l)) {
                    var m = b.replace(G, "");
                    s && !t.includes(m) || se(e, a, r, (m = l[b]).callable, m.delegationSelector)
                }
            }
        }, trigger(e, t, n) {
            if ("string" != typeof t || !e) return null;
            const i = $();
            let o = null, r = !0, s = !0, a = !1,
                l = (t !== ae(t) && i && (o = i.Event(t, n), i(e).trigger(o), r = !o.isPropagationStopped(), s = !o.isImmediatePropagationStopped(), a = o.isDefaultPrevented()), new Event(t, {
                    bubbles: r,
                    cancelable: !0
                }));
            return l = le(l, n), a && l.preventDefault(), s && e.dispatchEvent(l), l.defaultPrevented && o && o.preventDefault(), l
        }
    };

    function le(t, e) {
        for (const [n, i] of Object.entries(e || {})) try {
            t[n] = i
        } catch (e) {
            Object.defineProperty(t, n, {
                configurable: !0, get() {
                    return i
                }
            })
        }
        return t
    }

    const u = new Map, ce = {
        set(e, t, n) {
            u.has(e) || u.set(e, new Map);
            const i = u.get(e);
            i.has(t) || 0 === i.size ? i.set(t, n) : console.error(`Bootstrap doesn't allow more than one instance per element. Bound instance: ${Array.from(i.keys())[0]}.`)
        }, get(e, t) {
            return u.has(e) && u.get(e).get(t) || null
        }, remove(e, t) {
            if (u.has(e)) {
                const n = u.get(e);
                n.delete(t), 0 === n.size && u.delete(e)
            }
        }
    };

    function ue(t) {
        if ("true" === t) return !0;
        if ("false" === t) return !1;
        if (t === Number(t).toString()) return Number(t);
        if ("" === t || "null" === t) return null;
        if ("string" != typeof t) return t;
        try {
            return JSON.parse(decodeURIComponent(t))
        } catch (e) {
            return t
        }
    }

    function de(e) {
        return e.replace(/[A-Z]/g, e => "-" + e.toLowerCase())
    }

    const d = {
        setDataAttribute(e, t, n) {
            e.setAttribute("data-bs-" + de(t), n)
        }, removeDataAttribute(e, t) {
            e.removeAttribute("data-bs-" + de(t))
        }, getDataAttributes(t) {
            if (!t) return {};
            const n = {};
            for (const i of Object.keys(t.dataset).filter(e => e.startsWith("bs") && !e.startsWith("bsConfig"))) {
                let e = i.replace(/^bs/, "");
                e = e.charAt(0).toLowerCase() + e.slice(1, e.length), n[e] = ue(t.dataset[i])
            }
            return n
        }, getDataAttribute(e, t) {
            return ue(e.getAttribute("data-bs-" + de(t)))
        }
    };

    class fe {
        static get Default() {
            return {}
        }

        static get DefaultType() {
            return {}
        }

        static get NAME() {
            throw new Error('You have to implement the static method "NAME", for each component!')
        }

        _getConfig(e) {
            return e = this._mergeConfigObj(e), e = this._configAfterMerge(e), this._typeCheckConfig(e), e
        }

        _configAfterMerge(e) {
            return e
        }

        _mergeConfigObj(e, t) {
            var n = s(t) ? d.getDataAttribute(t, "config") : {};
            return {...this.constructor.Default, ..."object" == typeof n ? n : {}, ...s(t) ? d.getDataAttributes(t) : {}, ..."object" == typeof e ? e : {}}
        }

        _typeCheckConfig(e, t = this.constructor.DefaultType) {
            for (const o of Object.keys(t)) {
                var n = t[o], i = e[o],
                    i = s(i) ? "element" : null == (i = i) ? "" + i : Object.prototype.toString.call(i).match(/\s([a-z]+)/i)[1].toLowerCase();
                if (!new RegExp(n).test(i)) throw new TypeError(`${this.constructor.NAME.toUpperCase()}: Option "${o}" provided type "${i}" but expected type "${n}".`)
            }
        }
    }

    class t extends fe {
        constructor(e, t) {
            super(), (e = o(e)) && (this._element = e, this._config = this._getConfig(t), ce.set(this._element, this.constructor.DATA_KEY, this))
        }

        dispose() {
            ce.remove(this._element, this.constructor.DATA_KEY), y.off(this._element, this.constructor.EVENT_KEY);
            for (const e of Object.getOwnPropertyNames(this)) this[e] = null
        }

        _queueCallback(e, t, n = !0) {
            X(e, t, n)
        }

        _getConfig(e) {
            return e = this._mergeConfigObj(e, this._element), e = this._configAfterMerge(e), this._typeCheckConfig(e), e
        }

        static getInstance(e) {
            return ce.get(o(e), this.DATA_KEY)
        }

        static getOrCreateInstance(e, t = {}) {
            return this.getInstance(e) || new this(e, "object" == typeof t ? t : null)
        }

        static get VERSION() {
            return "5.2.0"
        }

        static get DATA_KEY() {
            return "bs." + this.NAME
        }

        static get EVENT_KEY() {
            return "." + this.DATA_KEY
        }

        static eventName(e) {
            return "" + e + this.EVENT_KEY
        }
    }

    var he = (n, i = "hide") => {
        var e = "click.dismiss" + n.EVENT_KEY;
        const o = n.NAME;
        y.on(document, e, `[data-bs-dismiss="${o}"]`, function (e) {
            if (["A", "AREA"].includes(this.tagName) && e.preventDefault(), !a(this)) {
                e = r(this) || this.closest("." + o);
                const t = n.getOrCreateInstance(e);
                t[i]()
            }
        })
    };

    class pe extends t {
        static get NAME() {
            return "alert"
        }

        close() {
            var e;
            y.trigger(this._element, "close.bs.alert").defaultPrevented || (this._element.classList.remove("show"), e = this._element.classList.contains("fade"), this._queueCallback(() => this._destroyElement(), this._element, e))
        }

        _destroyElement() {
            this._element.remove(), y.trigger(this._element, "closed.bs.alert"), this.dispose()
        }

        static jQueryInterface(t) {
            return this.each(function () {
                const e = pe.getOrCreateInstance(this);
                if ("string" == typeof t) {
                    if (void 0 === e[t] || t.startsWith("_") || "constructor" === t) throw new TypeError(`No method named "${t}"`);
                    e[t](this)
                }
            })
        }
    }

    he(pe, "close"), e(pe);
    const ge = '[data-bs-toggle="button"]';

    class me extends t {
        static get NAME() {
            return "button"
        }

        toggle() {
            this._element.setAttribute("aria-pressed", this._element.classList.toggle("active"))
        }

        static jQueryInterface(t) {
            return this.each(function () {
                const e = me.getOrCreateInstance(this);
                "toggle" === t && e[t]()
            })
        }
    }

    y.on(document, "click.bs.button.data-api", ge, e => {
        e.preventDefault();
        e = e.target.closest(ge);
        const t = me.getOrCreateInstance(e);
        t.toggle()
    }), e(me);
    const f = {
            find(e, t = document.documentElement) {
                return [].concat(...Element.prototype.querySelectorAll.call(t, e))
            }, findOne(e, t = document.documentElement) {
                return Element.prototype.querySelector.call(t, e)
            }, children(e, t) {
                return [].concat(...e.children).filter(e => e.matches(t))
            }, parents(e, t) {
                const n = [];
                let i = e.parentNode.closest(t);
                for (; i;) n.push(i), i = i.parentNode.closest(t);
                return n
            }, prev(e, t) {
                let n = e.previousElementSibling;
                for (; n;) {
                    if (n.matches(t)) return [n];
                    n = n.previousElementSibling
                }
                return []
            }, next(e, t) {
                let n = e.nextElementSibling;
                for (; n;) {
                    if (n.matches(t)) return [n];
                    n = n.nextElementSibling
                }
                return []
            }, focusableChildren(e) {
                var t = ["a", "button", "input", "textarea", "select", "details", "[tabindex]", '[contenteditable="true"]'].map(e => e + ':not([tabindex^="-"])').join(",");
                return this.find(t, e).filter(e => !a(e) && i(e))
            }
        }, n = ".bs.swipe", ve = (n, n, n, n, n, {endCallback: null, leftCallback: null, rightCallback: null}),
        ye = {endCallback: "(function|null)", leftCallback: "(function|null)", rightCallback: "(function|null)"};

    class be extends fe {
        constructor(e, t) {
            super(), (this._element = e) && be.isSupported() && (this._config = this._getConfig(t), this._deltaX = 0, this._supportPointerEvents = Boolean(window.PointerEvent), this._initEvents())
        }

        static get Default() {
            return ve
        }

        static get DefaultType() {
            return ye
        }

        static get NAME() {
            return "swipe"
        }

        dispose() {
            y.off(this._element, n)
        }

        _start(e) {
            this._supportPointerEvents ? this._eventIsPointerPenTouch(e) && (this._deltaX = e.clientX) : this._deltaX = e.touches[0].clientX
        }

        _end(e) {
            this._eventIsPointerPenTouch(e) && (this._deltaX = e.clientX - this._deltaX), this._handleSwipe(), c(this._config.endCallback)
        }

        _move(e) {
            this._deltaX = e.touches && 1 < e.touches.length ? 0 : e.touches[0].clientX - this._deltaX
        }

        _handleSwipe() {
            var e = Math.abs(this._deltaX);
            e <= 40 || (e = e / this._deltaX, this._deltaX = 0, e && c(0 < e ? this._config.rightCallback : this._config.leftCallback))
        }

        _initEvents() {
            this._supportPointerEvents ? (y.on(this._element, "pointerdown.bs.swipe", e => this._start(e)), y.on(this._element, "pointerup.bs.swipe", e => this._end(e)), this._element.classList.add("pointer-event")) : (y.on(this._element, "touchstart.bs.swipe", e => this._start(e)), y.on(this._element, "touchmove.bs.swipe", e => this._move(e)), y.on(this._element, "touchend.bs.swipe", e => this._end(e)))
        }

        _eventIsPointerPenTouch(e) {
            return this._supportPointerEvents && ("pen" === e.pointerType || "touch" === e.pointerType)
        }

        static isSupported() {
            return "ontouchstart" in document.documentElement || 0 < navigator.maxTouchPoints
        }
    }

    var h = ".bs.carousel";
    const _e = "next", p = "prev", g = "left", we = "right", xe = "slid" + h;
    const Te = "carousel", ke = "active", Ce = ".active", Ae = ".carousel-item";
    Ce, Ae;
    const Ee = {ArrowLeft: we, ArrowRight: g},
        Se = {interval: 5e3, keyboard: !0, pause: "hover", ride: !1, touch: !0, wrap: !0}, De = {
            interval: "(number|boolean)",
            keyboard: "boolean",
            pause: "(string|boolean)",
            ride: "(boolean|string)",
            touch: "boolean",
            wrap: "boolean"
        };

    class Le extends t {
        constructor(e, t) {
            super(e, t), this._interval = null, this._activeElement = null, this._isSliding = !1, this.touchTimeout = null, this._swipeHelper = null, this._indicatorsElement = f.findOne(".carousel-indicators", this._element), this._addEventListeners(), this._config.ride === Te && this.cycle()
        }

        static get Default() {
            return Se
        }

        static get DefaultType() {
            return De
        }

        static get NAME() {
            return "carousel"
        }

        next() {
            this._slide(_e)
        }

        nextWhenVisible() {
            !document.hidden && i(this._element) && this.next()
        }

        prev() {
            this._slide(p)
        }

        pause() {
            this._isSliding && B(this._element), this._clearInterval()
        }

        cycle() {
            this._clearInterval(), this._updateInterval(), this._interval = setInterval(() => this.nextWhenVisible(), this._config.interval)
        }

        _maybeEnableCycle() {
            this._config.ride && (this._isSliding ? y.one(this._element, xe, () => this.cycle()) : this.cycle())
        }

        to(e) {
            var t, n = this._getItems();
            e > n.length - 1 || e < 0 || (this._isSliding ? y.one(this._element, xe, () => this.to(e)) : (t = this._getItemIndex(this._getActive())) !== e && (t = t < e ? _e : p, this._slide(t, n[e])))
        }

        dispose() {
            this._swipeHelper && this._swipeHelper.dispose(), super.dispose()
        }

        _configAfterMerge(e) {
            return e.defaultInterval = e.interval, e
        }

        _addEventListeners() {
            this._config.keyboard && y.on(this._element, "keydown.bs.carousel", e => this._keydown(e)), "hover" === this._config.pause && (y.on(this._element, "mouseenter.bs.carousel", () => this.pause()), y.on(this._element, "mouseleave.bs.carousel", () => this._maybeEnableCycle())), this._config.touch && be.isSupported() && this._addTouchEventListeners()
        }

        _addTouchEventListeners() {
            for (const t of f.find(".carousel-item img", this._element)) y.on(t, "dragstart.bs.carousel", e => e.preventDefault());
            var e = {
                leftCallback: () => this._slide(this._directionToOrder(g)),
                rightCallback: () => this._slide(this._directionToOrder(we)),
                endCallback: () => {
                    "hover" === this._config.pause && (this.pause(), this.touchTimeout && clearTimeout(this.touchTimeout), this.touchTimeout = setTimeout(() => this._maybeEnableCycle(), 500 + this._config.interval))
                }
            };
            this._swipeHelper = new be(this._element, e)
        }

        _keydown(e) {
            var t;
            /input|textarea/i.test(e.target.tagName) || (t = Ee[e.key]) && (e.preventDefault(), this._slide(this._directionToOrder(t)))
        }

        _getItemIndex(e) {
            return this._getItems().indexOf(e)
        }

        _setActiveIndicatorElement(e) {
            if (this._indicatorsElement) {
                const t = f.findOne(Ce, this._indicatorsElement),
                    n = (t.classList.remove(ke), t.removeAttribute("aria-current"), f.findOne(`[data-bs-slide-to="${e}"]`, this._indicatorsElement));
                n && (n.classList.add(ke), n.setAttribute("aria-current", "true"))
            }
        }

        _updateInterval() {
            const e = this._activeElement || this._getActive();
            var t;
            e && (t = Number.parseInt(e.getAttribute("data-bs-interval"), 10), this._config.interval = t || this._config.defaultInterval)
        }

        _slide(t, e = null) {
            if (!this._isSliding) {
                const i = this._getActive();
                var n = t === _e;
                const o = e || U(this._getItems(), i, n, this._config.wrap);
                if (o !== i) {
                    const r = this._getItemIndex(o), s = e => y.trigger(this._element, e, {
                        relatedTarget: o,
                        direction: this._orderToDirection(t),
                        from: this._getItemIndex(i),
                        to: r
                    });
                    e = s("slide.bs.carousel");
                    if (!e.defaultPrevented && i && o) {
                        e = Boolean(this._interval);
                        this.pause(), this._isSliding = !0, this._setActiveIndicatorElement(r), this._activeElement = o;
                        const a = n ? "carousel-item-start" : "carousel-item-end",
                            l = n ? "carousel-item-next" : "carousel-item-prev";
                        o.classList.add(l), z(o), i.classList.add(a), o.classList.add(a);
                        this._queueCallback(() => {
                            o.classList.remove(a, l), o.classList.add(ke), i.classList.remove(ke, l, a), this._isSliding = !1, s(xe)
                        }, i, this._isAnimated()), e && this.cycle()
                    }
                }
            }
        }

        _isAnimated() {
            return this._element.classList.contains("slide")
        }

        _getActive() {
            return f.findOne(".active.carousel-item", this._element)
        }

        _getItems() {
            return f.find(Ae, this._element)
        }

        _clearInterval() {
            this._interval && (clearInterval(this._interval), this._interval = null)
        }

        _directionToOrder(e) {
            return l() ? e === g ? p : _e : e === g ? _e : p
        }

        _orderToDirection(e) {
            return l() ? e === p ? g : we : e === p ? we : g
        }

        static jQueryInterface(t) {
            return this.each(function () {
                const e = Le.getOrCreateInstance(this, t);
                if ("number" == typeof t) e.to(t); else if ("string" == typeof t) {
                    if (void 0 === e[t] || t.startsWith("_") || "constructor" === t) throw new TypeError(`No method named "${t}"`);
                    e[t]()
                }
            })
        }
    }

    y.on(document, "click.bs.carousel.data-api", "[data-bs-slide], [data-bs-slide-to]", function (e) {
        const t = r(this);
        if (t && t.classList.contains(Te)) {
            e.preventDefault();
            const n = Le.getOrCreateInstance(t);
            e = this.getAttribute("data-bs-slide-to");
            return e ? (n.to(e), void n._maybeEnableCycle()) : ("next" === d.getDataAttribute(this, "slide") ? n.next() : n.prev(), void n._maybeEnableCycle())
        }
    }), y.on(window, "load.bs.carousel.data-api", () => {
        for (const e of f.find('[data-bs-ride="carousel"]')) Le.getOrCreateInstance(e)
    }), e(Le);
    const Oe = "show", m = "collapse", Ne = "collapsing", je = (m, m, '[data-bs-toggle="collapse"]'),
        Ie = {parent: null, toggle: !0}, Pe = {parent: "(null|element)", toggle: "boolean"};

    class Me extends t {
        constructor(e, t) {
            super(e, t), this._isTransitioning = !1, this._triggerArray = [];
            for (const o of f.find(je)) {
                var n = R(o), i = f.find(n).filter(e => e === this._element);
                null !== n && i.length && this._triggerArray.push(o)
            }
            this._initializeChildren(), this._config.parent || this._addAriaAndCollapsedClass(this._triggerArray, this._isShown()), this._config.toggle && this.toggle()
        }

        static get Default() {
            return Ie
        }

        static get DefaultType() {
            return Pe
        }

        static get NAME() {
            return "collapse"
        }

        toggle() {
            this._isShown() ? this.hide() : this.show()
        }

        show() {
            if (!this._isTransitioning && !this._isShown()) {
                let e = [];
                if (!(e = this._config.parent ? this._getFirstLevelChildren(".collapse.show, .collapse.collapsing").filter(e => e !== this._element).map(e => Me.getOrCreateInstance(e, {toggle: !1})) : e).length || !e[0]._isTransitioning) {
                    var t = y.trigger(this._element, "show.bs.collapse");
                    if (!t.defaultPrevented) {
                        for (const i of e) i.hide();
                        const n = this._getDimension();
                        this._element.classList.remove(m), this._element.classList.add(Ne), this._element.style[n] = 0, this._addAriaAndCollapsedClass(this._triggerArray, !0), this._isTransitioning = !0;
                        t = "scroll" + (n[0].toUpperCase() + n.slice(1));
                        this._queueCallback(() => {
                            this._isTransitioning = !1, this._element.classList.remove(Ne), this._element.classList.add(m, Oe), this._element.style[n] = "", y.trigger(this._element, "shown.bs.collapse")
                        }, this._element, !0), this._element.style[n] = this._element[t] + "px"
                    }
                }
            }
        }

        hide() {
            if (!this._isTransitioning && this._isShown()) {
                var e = y.trigger(this._element, "hide.bs.collapse");
                if (!e.defaultPrevented) {
                    e = this._getDimension();
                    this._element.style[e] = this._element.getBoundingClientRect()[e] + "px", z(this._element), this._element.classList.add(Ne), this._element.classList.remove(m, Oe);
                    for (const n of this._triggerArray) {
                        var t = r(n);
                        t && !this._isShown(t) && this._addAriaAndCollapsedClass([n], !1)
                    }
                    this._isTransitioning = !0;
                    this._element.style[e] = "", this._queueCallback(() => {
                        this._isTransitioning = !1, this._element.classList.remove(Ne), this._element.classList.add(m), y.trigger(this._element, "hidden.bs.collapse")
                    }, this._element, !0)
                }
            }
        }

        _isShown(e = this._element) {
            return e.classList.contains(Oe)
        }

        _configAfterMerge(e) {
            return e.toggle = Boolean(e.toggle), e.parent = o(e.parent), e
        }

        _getDimension() {
            return this._element.classList.contains("collapse-horizontal") ? "width" : "height"
        }

        _initializeChildren() {
            if (this._config.parent) for (const t of this._getFirstLevelChildren(je)) {
                var e = r(t);
                e && this._addAriaAndCollapsedClass([t], this._isShown(e))
            }
        }

        _getFirstLevelChildren(e) {
            const t = f.find(":scope .collapse .collapse", this._config.parent);
            return f.find(e, this._config.parent).filter(e => !t.includes(e))
        }

        _addAriaAndCollapsedClass(e, t) {
            if (e.length) for (const n of e) n.classList.toggle("collapsed", !t), n.setAttribute("aria-expanded", t)
        }

        static jQueryInterface(t) {
            const n = {};
            return "string" == typeof t && /show|hide/.test(t) && (n.toggle = !1), this.each(function () {
                const e = Me.getOrCreateInstance(this, n);
                if ("string" == typeof t) {
                    if (void 0 === e[t]) throw new TypeError(`No method named "${t}"`);
                    e[t]()
                }
            })
        }
    }

    y.on(document, "click.bs.collapse.data-api", je, function (e) {
        ("A" === e.target.tagName || e.delegateTarget && "A" === e.delegateTarget.tagName) && e.preventDefault();
        e = R(this);
        for (const t of f.find(e)) Me.getOrCreateInstance(t, {toggle: !1}).toggle()
    }), e(Me);
    var E = "top", S = "bottom", D = "right", L = "left", He = "auto", O = [E, S, D, L], N = "start", b = "end",
        qe = "clippingParents", Re = "viewport", Be = "popper", Fe = "reference", We = O.reduce(function (e, t) {
            return e.concat([t + "-" + N, t + "-" + b])
        }, []), ze = [].concat(O, [He]).reduce(function (e, t) {
            return e.concat([t, t + "-" + N, t + "-" + b])
        }, []), h = "beforeRead", $e = "afterRead", Ve = "beforeMain", Xe = "afterMain", Ue = "beforeWrite",
        Ye = "afterWrite", Qe = [h, "read", $e, Ve, "main", Xe, Ue, "write", Ye];

    function v(e) {
        return e ? (e.nodeName || "").toLowerCase() : null
    }

    function _(e) {
        return null == e ? window : "[object Window]" !== e.toString() ? (t = e.ownerDocument) && t.defaultView || window : e;
        var t
    }

    function Ge(e) {
        return e instanceof _(e).Element || e instanceof Element
    }

    function w(e) {
        return e instanceof _(e).HTMLElement || e instanceof HTMLElement
    }

    function Ke(e) {
        if ("undefined" != typeof ShadowRoot) return e instanceof _(e).ShadowRoot || e instanceof ShadowRoot
    }

    var Je = {
        name: "applyStyles", enabled: !0, phase: "write", fn: function (e) {
            var o = e.state;
            Object.keys(o.elements).forEach(function (e) {
                var t = o.styles[e] || {}, n = o.attributes[e] || {}, i = o.elements[e];
                w(i) && v(i) && (Object.assign(i.style, t), Object.keys(n).forEach(function (e) {
                    var t = n[e];
                    !1 === t ? i.removeAttribute(e) : i.setAttribute(e, !0 === t ? "" : t)
                }))
            })
        }, effect: function (e) {
            var i = e.state, o = {
                popper: {position: i.options.strategy, left: "0", top: "0", margin: "0"},
                arrow: {position: "absolute"},
                reference: {}
            };
            return Object.assign(i.elements.popper.style, o.popper), i.styles = o, i.elements.arrow && Object.assign(i.elements.arrow.style, o.arrow), function () {
                Object.keys(i.elements).forEach(function (e) {
                    var t = i.elements[e], n = i.attributes[e] || {},
                        e = Object.keys((i.styles.hasOwnProperty(e) ? i.styles : o)[e]).reduce(function (e, t) {
                            return e[t] = "", e
                        }, {});
                    w(t) && v(t) && (Object.assign(t.style, e), Object.keys(n).forEach(function (e) {
                        t.removeAttribute(e)
                    }))
                })
            }
        }, requires: ["computeStyles"]
    };

    function j(e) {
        return e.split("-")[0]
    }

    var A = Math.max, Ze = Math.min, et = Math.round;

    function tt(e, t) {
        void 0 === t && (t = !1);
        var n = e.getBoundingClientRect(), i = 1, o = 1;
        return w(e) && t && (t = e.offsetHeight, 0 < (e = e.offsetWidth) && (i = et(n.width) / e || 1), 0 < t && (o = et(n.height) / t || 1)), {
            width: n.width / i,
            height: n.height / o,
            top: n.top / o,
            right: n.right / i,
            bottom: n.bottom / o,
            left: n.left / i,
            x: n.left / i,
            y: n.top / o
        }
    }

    function nt(e) {
        var t = tt(e), n = e.offsetWidth, i = e.offsetHeight;
        return Math.abs(t.width - n) <= 1 && (n = t.width), Math.abs(t.height - i) <= 1 && (i = t.height), {
            x: e.offsetLeft,
            y: e.offsetTop,
            width: n,
            height: i
        }
    }

    function it(e, t) {
        var n = t.getRootNode && t.getRootNode();
        if (e.contains(t)) return !0;
        if (n && Ke(n)) {
            var i = t;
            do {
                if (i && e.isSameNode(i)) return !0
            } while (i = i.parentNode || i.host)
        }
        return !1
    }

    function x(e) {
        return _(e).getComputedStyle(e)
    }

    function T(e) {
        return ((Ge(e) ? e.ownerDocument : e.document) || window.document).documentElement
    }

    function ot(e) {
        return "html" === v(e) ? e : e.assignedSlot || e.parentNode || (Ke(e) ? e.host : null) || T(e)
    }

    function rt(e) {
        return w(e) && "fixed" !== x(e).position ? e.offsetParent : null
    }

    function st(e) {
        for (var t, n = _(e), i = rt(e); i && (t = i, 0 <= ["table", "td", "th"].indexOf(v(t))) && "static" === x(i).position;) i = rt(i);
        return (!i || "html" !== v(i) && ("body" !== v(i) || "static" !== x(i).position)) && (i || function (e) {
            var t = -1 !== navigator.userAgent.toLowerCase().indexOf("firefox"),
                n = -1 !== navigator.userAgent.indexOf("Trident");
            if (n && w(e) && "fixed" === x(e).position) return null;
            var i = ot(e);
            for (Ke(i) && (i = i.host); w(i) && ["html", "body"].indexOf(v(i)) < 0;) {
                var o = x(i);
                if ("none" !== o.transform || "none" !== o.perspective || "paint" === o.contain || -1 !== ["transform", "perspective"].indexOf(o.willChange) || t && "filter" === o.willChange || t && o.filter && "none" !== o.filter) return i;
                i = i.parentNode
            }
            return null
        }(e)) || n
    }

    function at(e) {
        return 0 <= ["top", "bottom"].indexOf(e) ? "x" : "y"
    }

    function lt(e, t, n) {
        return A(e, Ze(t, n))
    }

    function ct() {
        return {top: 0, right: 0, bottom: 0, left: 0}
    }

    function ut(e) {
        return Object.assign({}, ct(), e)
    }

    function dt(n, e) {
        return e.reduce(function (e, t) {
            return e[t] = n, e
        }, {})
    }

    var ft = {
        name: "arrow", enabled: !0, phase: "main", fn: function (e) {
            var t, n, i, o, r = e.state, s = e.name, e = e.options, a = r.elements.arrow,
                l = r.modifiersData.popperOffsets, c = at(u = j(r.placement)),
                u = 0 <= [L, D].indexOf(u) ? "height" : "width";
            a && l && (e = e.padding, n = r, n = ut("number" != typeof (e = "function" == typeof e ? e(Object.assign({}, n.rects, {placement: n.placement})) : e) ? e : dt(e, O)), e = nt(a), o = "y" === c ? E : L, i = "y" === c ? S : D, t = r.rects.reference[u] + r.rects.reference[c] - l[c] - r.rects.popper[u], l = l[c] - r.rects.reference[c], a = (a = st(a)) ? "y" === c ? a.clientHeight || 0 : a.clientWidth || 0 : 0, o = n[o], n = a - e[u] - n[i], o = lt(o, i = a / 2 - e[u] / 2 + (t / 2 - l / 2), n), r.modifiersData[s] = ((a = {})[c] = o, a.centerOffset = o - i, a))
        }, effect: function (e) {
            var t = e.state;
            null != (e = void 0 === (e = e.options.element) ? "[data-popper-arrow]" : e) && ("string" != typeof e || (e = t.elements.popper.querySelector(e))) && it(t.elements.popper, e) && (t.elements.arrow = e)
        }, requires: ["popperOffsets"], requiresIfExists: ["preventOverflow"]
    };

    function ht(e) {
        return e.split("-")[1]
    }

    var pt = {top: "auto", right: "auto", bottom: "auto", left: "auto"};

    function gt(e) {
        var t, n, i, o = e.popper, r = e.popperRect, s = e.placement, a = e.variation, l = e.offsets, c = e.position,
            u = e.gpuAcceleration, d = e.adaptive, f = e.roundOffsets, e = e.isFixed, h = l.x, h = void 0 === h ? 0 : h,
            p = l.y, p = void 0 === p ? 0 : p, g = "function" == typeof f ? f({x: h, y: p}) : {x: h, y: p},
            g = (h = g.x, p = g.y, l.hasOwnProperty("x")), l = l.hasOwnProperty("y"), m = L, v = E, y = window,
            o = (d && (n = "clientHeight", t = "clientWidth", (i = st(o)) === _(o) && "static" !== x(i = T(o)).position && "absolute" === c && (n = "scrollHeight", t = "scrollWidth"), s !== E && (s !== L && s !== D || a !== b) || (v = S, p = (p - ((e && i === y && y.visualViewport ? y.visualViewport.height : i[n]) - r.height)) * (u ? 1 : -1)), s !== L && (s !== E && s !== S || a !== b) || (m = D, h = (h - ((e && i === y && y.visualViewport ? y.visualViewport.width : i[t]) - r.width)) * (u ? 1 : -1))), Object.assign({position: c}, d && pt)),
            e = !0 === f ? (s = (n = {x: h, y: p}).x, n = n.y, a = window.devicePixelRatio || 1, {
                x: et(s * a) / a || 0,
                y: et(n * a) / a || 0
            }) : {x: h, y: p};
        return h = e.x, p = e.y, u ? Object.assign({}, o, ((i = {})[v] = l ? "0" : "", i[m] = g ? "0" : "", i.transform = (y.devicePixelRatio || 1) <= 1 ? "translate(" + h + "px, " + p + "px)" : "translate3d(" + h + "px, " + p + "px, 0)", i)) : Object.assign({}, o, ((t = {})[v] = l ? p + "px" : "", t[m] = g ? h + "px" : "", t.transform = "", t))
    }

    var mt = {
        name: "computeStyles", enabled: !0, phase: "beforeWrite", fn: function (e) {
            var t = e.state, e = e.options, n = void 0 === (n = e.gpuAcceleration) || n,
                i = void 0 === (i = e.adaptive) || i, e = void 0 === (e = e.roundOffsets) || e, n = {
                    placement: j(t.placement),
                    variation: ht(t.placement),
                    popper: t.elements.popper,
                    popperRect: t.rects.popper,
                    gpuAcceleration: n,
                    isFixed: "fixed" === t.options.strategy
                };
            null != t.modifiersData.popperOffsets && (t.styles.popper = Object.assign({}, t.styles.popper, gt(Object.assign({}, n, {
                offsets: t.modifiersData.popperOffsets,
                position: t.options.strategy,
                adaptive: i,
                roundOffsets: e
            })))), null != t.modifiersData.arrow && (t.styles.arrow = Object.assign({}, t.styles.arrow, gt(Object.assign({}, n, {
                offsets: t.modifiersData.arrow,
                position: "absolute",
                adaptive: !1,
                roundOffsets: e
            })))), t.attributes.popper = Object.assign({}, t.attributes.popper, {"data-popper-placement": t.placement})
        }, data: {}
    }, vt = {passive: !0};
    var yt = {
        name: "eventListeners", enabled: !0, phase: "write", fn: function () {
        }, effect: function (e) {
            var t = e.state, n = e.instance, i = (e = e.options).scroll, o = void 0 === i || i,
                r = void 0 === (i = e.resize) || i, s = _(t.elements.popper),
                a = [].concat(t.scrollParents.reference, t.scrollParents.popper);
            return o && a.forEach(function (e) {
                e.addEventListener("scroll", n.update, vt)
            }), r && s.addEventListener("resize", n.update, vt), function () {
                o && a.forEach(function (e) {
                    e.removeEventListener("scroll", n.update, vt)
                }), r && s.removeEventListener("resize", n.update, vt)
            }
        }, data: {}
    }, bt = {left: "right", right: "left", bottom: "top", top: "bottom"};

    function _t(e) {
        return e.replace(/left|right|bottom|top/g, function (e) {
            return bt[e]
        })
    }

    var wt = {start: "end", end: "start"};

    function xt(e) {
        return e.replace(/start|end/g, function (e) {
            return wt[e]
        })
    }

    function Tt(e) {
        e = _(e);
        return {scrollLeft: e.pageXOffset, scrollTop: e.pageYOffset}
    }

    function kt(e) {
        return tt(T(e)).left + Tt(e).scrollLeft
    }

    function Ct(e) {
        var e = x(e), t = e.overflow, n = e.overflowX, e = e.overflowY;
        return /auto|scroll|overlay|hidden/.test(t + e + n)
    }

    function At(e, t) {
        void 0 === t && (t = []);
        var n = function e(t) {
                return 0 <= ["html", "body", "#document"].indexOf(v(t)) ? t.ownerDocument.body : w(t) && Ct(t) ? t : e(ot(t))
            }(e), e = n === (null == (e = e.ownerDocument) ? void 0 : e.body), i = _(n),
            i = e ? [i].concat(i.visualViewport || [], Ct(n) ? n : []) : n, n = t.concat(i);
        return e ? n : n.concat(At(ot(i)))
    }

    function Et(e) {
        return Object.assign({}, e, {left: e.x, top: e.y, right: e.x + e.width, bottom: e.y + e.height})
    }

    function St(e, t) {
        return t === Re ? Et((i = _(n = e), o = T(n), i = i.visualViewport, r = o.clientWidth, o = o.clientHeight, a = s = 0, i && (r = i.width, o = i.height, /^((?!chrome|android).)*safari/i.test(navigator.userAgent) || (s = i.offsetLeft, a = i.offsetTop)), {
            width: r,
            height: o,
            x: s + kt(n),
            y: a
        })) : Ge(t) ? ((r = tt(i = t)).top = r.top + i.clientTop, r.left = r.left + i.clientLeft, r.bottom = r.top + i.clientHeight, r.right = r.left + i.clientWidth, r.width = i.clientWidth, r.height = i.clientHeight, r.x = r.left, r.y = r.top, r) : Et((o = T(e), s = T(o), n = Tt(o), a = null == (a = o.ownerDocument) ? void 0 : a.body, t = A(s.scrollWidth, s.clientWidth, a ? a.scrollWidth : 0, a ? a.clientWidth : 0), e = A(s.scrollHeight, s.clientHeight, a ? a.scrollHeight : 0, a ? a.clientHeight : 0), o = -n.scrollLeft + kt(o), n = -n.scrollTop, "rtl" === x(a || s).direction && (o += A(s.clientWidth, a ? a.clientWidth : 0) - t), {
            width: t,
            height: e,
            x: o,
            y: n
        }));
        var n, i, o, r, s, a
    }

    function Dt(n, e, t) {
        var i,
            o = "clippingParents" === e ? (r = At(ot(o = n)), Ge(i = 0 <= ["absolute", "fixed"].indexOf(x(o).position) && w(o) ? st(o) : o) ? r.filter(function (e) {
                return Ge(e) && it(e, i) && "body" !== v(e)
            }) : []) : [].concat(e), r = [].concat(o, [t]), e = r[0], t = r.reduce(function (e, t) {
                t = St(n, t);
                return e.top = A(t.top, e.top), e.right = Ze(t.right, e.right), e.bottom = Ze(t.bottom, e.bottom), e.left = A(t.left, e.left), e
            }, St(n, e));
        return t.width = t.right - t.left, t.height = t.bottom - t.top, t.x = t.left, t.y = t.top, t
    }

    function Lt(e) {
        var t, n = e.reference, i = e.element, e = e.placement, o = e ? j(e) : null, e = e ? ht(e) : null,
            r = n.x + n.width / 2 - i.width / 2, s = n.y + n.height / 2 - i.height / 2;
        switch (o) {
            case E:
                t = {x: r, y: n.y - i.height};
                break;
            case S:
                t = {x: r, y: n.y + n.height};
                break;
            case D:
                t = {x: n.x + n.width, y: s};
                break;
            case L:
                t = {x: n.x - i.width, y: s};
                break;
            default:
                t = {x: n.x, y: n.y}
        }
        var a = o ? at(o) : null;
        if (null != a) {
            var l = "y" === a ? "height" : "width";
            switch (e) {
                case N:
                    t[a] = t[a] - (n[l] / 2 - i[l] / 2);
                    break;
                case b:
                    t[a] = t[a] + (n[l] / 2 - i[l] / 2)
            }
        }
        return t
    }

    function Ot(e, t) {
        var i, t = t = void 0 === t ? {} : t, n = t.placement, n = void 0 === n ? e.placement : n, o = t.boundary,
            o = void 0 === o ? qe : o, r = t.rootBoundary, r = void 0 === r ? Re : r, s = t.elementContext,
            s = void 0 === s ? Be : s, a = t.altBoundary, a = void 0 !== a && a, t = t.padding,
            t = void 0 === t ? 0 : t, t = ut("number" != typeof t ? t : dt(t, O)), l = e.rects.popper,
            a = e.elements[a ? s === Be ? Fe : Be : s],
            a = Dt(Ge(a) ? a : a.contextElement || T(e.elements.popper), o, r), o = tt(e.elements.reference),
            r = Lt({reference: o, element: l, strategy: "absolute", placement: n}), l = Et(Object.assign({}, l, r)),
            r = s === Be ? l : o, c = {
                top: a.top - r.top + t.top,
                bottom: r.bottom - a.bottom + t.bottom,
                left: a.left - r.left + t.left,
                right: r.right - a.right + t.right
            }, l = e.modifiersData.offset;
        return s === Be && l && (i = l[n], Object.keys(c).forEach(function (e) {
            var t = 0 <= [D, S].indexOf(e) ? 1 : -1, n = 0 <= [E, S].indexOf(e) ? "y" : "x";
            c[e] += i[n] * t
        })), c
    }

    var Nt = {
        name: "flip", enabled: !0, phase: "main", fn: function (e) {
            var d = e.state, t = e.options, e = e.name;
            if (!d.modifiersData[e]._skip) {
                for (var n = t.mainAxis, i = void 0 === n || n, n = t.altAxis, o = void 0 === n || n, n = t.fallbackPlacements, f = t.padding, h = t.boundary, p = t.rootBoundary, r = t.altBoundary, s = t.flipVariations, g = void 0 === s || s, m = t.allowedAutoPlacements, s = d.options.placement, t = j(s), n = n || (t === s || !g ? [_t(s)] : function (e) {
                    if (j(e) === He) return [];
                    var t = _t(e);
                    return [xt(e), t, xt(t)]
                }(s)), a = [s].concat(n).reduce(function (e, t) {
                    return e.concat(j(t) === He ? (n = d, i = (e = e = void 0 === (e = {
                        placement: t,
                        boundary: h,
                        rootBoundary: p,
                        padding: f,
                        flipVariations: g,
                        allowedAutoPlacements: m
                    }) ? {} : e).placement, o = e.boundary, r = e.rootBoundary, s = e.padding, a = e.flipVariations, l = void 0 === (e = e.allowedAutoPlacements) ? ze : e, c = ht(i), e = c ? a ? We : We.filter(function (e) {
                        return ht(e) === c
                    }) : O, u = (i = 0 === (i = e.filter(function (e) {
                        return 0 <= l.indexOf(e)
                    })).length ? e : i).reduce(function (e, t) {
                        return e[t] = Ot(n, {placement: t, boundary: o, rootBoundary: r, padding: s})[j(t)], e
                    }, {}), Object.keys(u).sort(function (e, t) {
                        return u[e] - u[t]
                    })) : t);
                    var n, i, o, r, s, a, l, c, u
                }, []), l = d.rects.reference, c = d.rects.popper, u = new Map, v = !0, y = a[0], b = 0; b < a.length; b++) {
                    var _ = a[b], w = j(_), x = ht(_) === N, T = 0 <= [E, S].indexOf(w), k = T ? "width" : "height",
                        C = Ot(d, {placement: _, boundary: h, rootBoundary: p, altBoundary: r, padding: f}),
                        T = T ? x ? D : L : x ? S : E, x = (l[k] > c[k] && (T = _t(T)), _t(T)), k = [];
                    if (i && k.push(C[w] <= 0), o && k.push(C[T] <= 0, C[x] <= 0), k.every(function (e) {
                        return e
                    })) {
                        y = _, v = !1;
                        break
                    }
                    u.set(_, k)
                }
                if (v) for (var A = g ? 3 : 1; 0 < A; A--) if ("break" === function (t) {
                    var e = a.find(function (e) {
                        e = u.get(e);
                        if (e) return e.slice(0, t).every(function (e) {
                            return e
                        })
                    });
                    if (e) return y = e, "break"
                }(A)) break;
                d.placement !== y && (d.modifiersData[e]._skip = !0, d.placement = y, d.reset = !0)
            }
        }, requiresIfExists: ["offset"], data: {_skip: !1}
    };

    function jt(e, t, n) {
        return {
            top: e.top - t.height - (n = void 0 === n ? {x: 0, y: 0} : n).y,
            right: e.right - t.width + n.x,
            bottom: e.bottom - t.height + n.y,
            left: e.left - t.width - n.x
        }
    }

    function It(t) {
        return [E, D, S, L].some(function (e) {
            return 0 <= t[e]
        })
    }

    var Pt = {
        name: "hide", enabled: !0, phase: "main", requiresIfExists: ["preventOverflow"], fn: function (e) {
            var t = e.state, e = e.name, n = t.rects.reference, i = t.rects.popper, o = t.modifiersData.preventOverflow,
                r = Ot(t, {elementContext: "reference"}), s = Ot(t, {altBoundary: !0}), r = jt(r, n), n = jt(s, i, o),
                s = It(r), i = It(n);
            t.modifiersData[e] = {
                referenceClippingOffsets: r,
                popperEscapeOffsets: n,
                isReferenceHidden: s,
                hasPopperEscaped: i
            }, t.attributes.popper = Object.assign({}, t.attributes.popper, {
                "data-popper-reference-hidden": s,
                "data-popper-escaped": i
            })
        }
    };
    var Mt = {
        name: "offset", enabled: !0, phase: "main", requires: ["popperOffsets"], fn: function (e) {
            var s = e.state, t = e.options, e = e.name, a = void 0 === (t = t.offset) ? [0, 0] : t,
                t = ze.reduce(function (e, t) {
                    var n, i, o, r;
                    return e[t] = (t = t, n = s.rects, i = a, o = j(t), r = 0 <= [L, E].indexOf(o) ? -1 : 1, t = (n = "function" == typeof i ? i(Object.assign({}, n, {placement: t})) : i)[0] || 0, i = (n[1] || 0) * r, 0 <= [L, D].indexOf(o) ? {
                        x: i,
                        y: t
                    } : {x: t, y: i}), e
                }, {}), n = (i = t[s.placement]).x, i = i.y;
            null != s.modifiersData.popperOffsets && (s.modifiersData.popperOffsets.x += n, s.modifiersData.popperOffsets.y += i), s.modifiersData[e] = t
        }
    };
    var Ht = {
        name: "popperOffsets", enabled: !0, phase: "read", fn: function (e) {
            var t = e.state, e = e.name;
            t.modifiersData[e] = Lt({
                reference: t.rects.reference,
                element: t.rects.popper,
                strategy: "absolute",
                placement: t.placement
            })
        }, data: {}
    };
    var qt = {
        name: "preventOverflow", enabled: !0, phase: "main", fn: function (e) {
            var t, n, i, o, r, s, a, l, c, u = e.state, d = e.options, e = e.name, f = void 0 === (f = d.mainAxis) || f,
                h = void 0 !== (h = d.altAxis) && h, p = d.boundary, g = d.rootBoundary, m = d.altBoundary,
                v = d.padding, y = void 0 === (y = d.tether) || y, d = void 0 === (d = d.tetherOffset) ? 0 : d,
                p = Ot(u, {boundary: p, rootBoundary: g, padding: v, altBoundary: m}), g = j(u.placement),
                m = !(v = ht(u.placement)), b = at(g), _ = "x" === b ? "y" : "x", w = u.modifiersData.popperOffsets,
                x = u.rects.reference, T = u.rects.popper,
                d = "number" == typeof (d = "function" == typeof d ? d(Object.assign({}, u.rects, {placement: u.placement})) : d) ? {
                    mainAxis: d,
                    altAxis: d
                } : Object.assign({mainAxis: 0, altAxis: 0}, d),
                k = u.modifiersData.offset ? u.modifiersData.offset[u.placement] : null, C = {x: 0, y: 0};
            w && (f && (f = "y" === b ? "height" : "width", s = (a = w[b]) + p[n = "y" === b ? E : L], l = a - p[c = "y" === b ? S : D], t = y ? -T[f] / 2 : 0, o = (v === N ? x : T)[f], v = v === N ? -T[f] : -x[f], r = u.elements.arrow, r = y && r ? nt(r) : {
                width: 0,
                height: 0
            }, n = (i = u.modifiersData["arrow#persistent"] ? u.modifiersData["arrow#persistent"].padding : ct())[n], i = i[c], c = lt(0, x[f], r[f]), r = m ? x[f] / 2 - t - c - n - d.mainAxis : o - c - n - d.mainAxis, o = m ? -x[f] / 2 + t + c + i + d.mainAxis : v + c + i + d.mainAxis, m = (n = u.elements.arrow && st(u.elements.arrow)) ? "y" === b ? n.clientTop || 0 : n.clientLeft || 0 : 0, v = a + o - (t = null != (f = null == k ? void 0 : k[b]) ? f : 0), c = lt(y ? Ze(s, a + r - t - m) : s, a, y ? A(l, v) : l), w[b] = c, C[b] = c - a), h && (i = "y" == _ ? "height" : "width", o = (n = w[_]) + p["x" === b ? E : L], f = n - p["x" === b ? S : D], r = -1 !== [E, L].indexOf(g), m = null != (t = null == k ? void 0 : k[_]) ? t : 0, s = r ? o : n - x[i] - T[i] - m + d.altAxis, v = r ? n + x[i] + T[i] - m - d.altAxis : f, a = y && r ? (l = lt(l = s, n, c = v), c < l ? c : l) : lt(y ? s : o, n, y ? v : f), w[_] = a, C[_] = a - n), u.modifiersData[e] = C)
        }, requiresIfExists: ["offset"]
    };

    function Rt(e, t, n) {
        void 0 === n && (n = !1);
        var i = w(t),
            o = w(t) && (s = (o = t).getBoundingClientRect(), r = et(s.width) / o.offsetWidth || 1, s = et(s.height) / o.offsetHeight || 1, 1 !== r || 1 !== s),
            r = T(t), s = tt(e, o), e = {scrollLeft: 0, scrollTop: 0}, a = {x: 0, y: 0};
        return !i && n || ("body" === v(t) && !Ct(r) || (e = (i = t) !== _(i) && w(i) ? {
            scrollLeft: i.scrollLeft,
            scrollTop: i.scrollTop
        } : Tt(i)), w(t) ? ((a = tt(t, !0)).x += t.clientLeft, a.y += t.clientTop) : r && (a.x = kt(r))), {
            x: s.left + e.scrollLeft - a.x,
            y: s.top + e.scrollTop - a.y,
            width: s.width,
            height: s.height
        }
    }

    function Bt(e) {
        var n = new Map, i = new Set, o = [];
        return e.forEach(function (e) {
            n.set(e.name, e)
        }), e.forEach(function (e) {
            i.has(e.name) || !function t(e) {
                i.add(e.name), [].concat(e.requires || [], e.requiresIfExists || []).forEach(function (e) {
                    i.has(e) || (e = n.get(e)) && t(e)
                }), o.push(e)
            }(e)
        }), o
    }

    var Ft = {placement: "bottom", modifiers: [], strategy: "absolute"};

    function Wt() {
        for (var e = arguments.length, t = new Array(e), n = 0; n < e; n++) t[n] = arguments[n];
        return !t.some(function (e) {
            return !(e && "function" == typeof e.getBoundingClientRect)
        })
    }

    function zt(e) {
        var e = e = void 0 === e ? {} : e, t = e.defaultModifiers, d = void 0 === t ? [] : t, t = e.defaultOptions,
            f = void 0 === t ? Ft : t;
        return function (i, o, t) {
            void 0 === t && (t = f);
            var n, r, s = {
                placement: "bottom",
                orderedModifiers: [],
                options: Object.assign({}, Ft, f),
                modifiersData: {},
                elements: {reference: i, popper: o},
                attributes: {},
                styles: {}
            }, a = [], l = !1, c = {
                state: s, setOptions: function (e) {
                    var n, t, e = "function" == typeof e ? e(s.options) : e,
                        e = (u(), s.options = Object.assign({}, f, s.options, e), s.scrollParents = {
                            reference: Ge(i) ? At(i) : i.contextElement ? At(i.contextElement) : [],
                            popper: At(o)
                        }, e = [].concat(d, s.options.modifiers), t = e.reduce(function (e, t) {
                            var n = e[t.name];
                            return e[t.name] = n ? Object.assign({}, n, t, {
                                options: Object.assign({}, n.options, t.options),
                                data: Object.assign({}, n.data, t.data)
                            }) : t, e
                        }, {}), e = Object.keys(t).map(function (e) {
                            return t[e]
                        }), n = Bt(e), Qe.reduce(function (e, t) {
                            return e.concat(n.filter(function (e) {
                                return e.phase === t
                            }))
                        }, []));
                    return s.orderedModifiers = e.filter(function (e) {
                        return e.enabled
                    }), s.orderedModifiers.forEach(function (e) {
                        var t = e.name, n = e.options, e = e.effect;
                        "function" == typeof e && (e = e({
                            state: s,
                            name: t,
                            instance: c,
                            options: void 0 === n ? {} : n
                        }), a.push(e || function () {
                        }))
                    }), c.update()
                }, forceUpdate: function () {
                    if (!l) {
                        var e = s.elements, t = e.reference, e = e.popper;
                        if (Wt(t, e)) {
                            s.rects = {
                                reference: Rt(t, st(e), "fixed" === s.options.strategy),
                                popper: nt(e)
                            }, s.reset = !1, s.placement = s.options.placement, s.orderedModifiers.forEach(function (e) {
                                return s.modifiersData[e.name] = Object.assign({}, e.data)
                            });
                            for (var n, i, o, r = 0; r < s.orderedModifiers.length; r++) !0 === s.reset ? (s.reset = !1, r = -1) : (n = (o = s.orderedModifiers[r]).fn, i = o.options, o = o.name, "function" == typeof n && (s = n({
                                state: s,
                                options: void 0 === i ? {} : i,
                                name: o,
                                instance: c
                            }) || s))
                        }
                    }
                }, update: (n = function () {
                    return new Promise(function (e) {
                        c.forceUpdate(), e(s)
                    })
                }, function () {
                    return r = r || new Promise(function (e) {
                        Promise.resolve().then(function () {
                            r = void 0, e(n())
                        })
                    })
                }), destroy: function () {
                    u(), l = !0
                }
            };
            return Wt(i, o) && c.setOptions(t).then(function (e) {
                !l && t.onFirstUpdate && t.onFirstUpdate(e)
            }), c;

            function u() {
                a.forEach(function (e) {
                    return e()
                }), a = []
            }
        }
    }

    var $t = zt({defaultModifiers: [yt, Ht, mt, Je, Mt, Nt, qt, ft, Pt]});
    const Vt = Object.freeze(Object.defineProperty({
        __proto__: null,
        popperGenerator: zt,
        detectOverflow: Ot,
        createPopperBase: zt(),
        createPopper: $t,
        createPopperLite: zt({defaultModifiers: [yt, Ht, mt, Je]}),
        top: E,
        bottom: S,
        right: D,
        left: L,
        auto: He,
        basePlacements: O,
        start: N,
        end: b,
        clippingParents: qe,
        viewport: Re,
        popper: Be,
        reference: Fe,
        variationPlacements: We,
        placements: ze,
        beforeRead: h,
        read: "read",
        afterRead: $e,
        beforeMain: Ve,
        main: "main",
        afterMain: Xe,
        beforeWrite: Ue,
        write: "write",
        afterWrite: Ye,
        modifierPhases: Qe,
        applyStyles: Je,
        arrow: ft,
        computeStyles: mt,
        eventListeners: yt,
        flip: Nt,
        hide: Pt,
        offset: Mt,
        popperOffsets: Ht,
        preventOverflow: qt
    }, Symbol.toStringTag, {value: "Module"})), Xt = "dropdown";
    h = ".bs.dropdown", $e = ".data-api";
    const Ut = "ArrowDown";
    Ve = "click" + h + $e, Xe = "keydown" + h + $e;
    const Yt = "show", Qt = '[data-bs-toggle="dropdown"]:not(.disabled):not(:disabled)', Gt = (Qt, ".dropdown-menu"),
        Kt = l() ? "top-end" : "top-start", Jt = l() ? "top-start" : "top-end",
        Zt = l() ? "bottom-end" : "bottom-start", en = l() ? "bottom-start" : "bottom-end",
        tn = l() ? "left-start" : "right-start", nn = l() ? "right-start" : "left-start", on = {
            autoClose: !0,
            boundary: "clippingParents",
            display: "dynamic",
            offset: [0, 2],
            popperConfig: null,
            reference: "toggle"
        }, rn = {
            autoClose: "(boolean|string)",
            boundary: "(string|element)",
            display: "string",
            offset: "(array|string|function)",
            popperConfig: "(null|object|function)",
            reference: "(string|element|object)"
        };

    class k extends t {
        constructor(e, t) {
            super(e, t), this._popper = null, this._parent = this._element.parentNode, this._menu = f.findOne(Gt, this._parent), this._inNavbar = this._detectNavbar()
        }

        static get Default() {
            return on
        }

        static get DefaultType() {
            return rn
        }

        static get NAME() {
            return Xt
        }

        toggle() {
            return this._isShown() ? this.hide() : this.show()
        }

        show() {
            if (!a(this._element) && !this._isShown()) {
                var e = {relatedTarget: this._element}, t = y.trigger(this._element, "show.bs.dropdown", e);
                if (!t.defaultPrevented) {
                    if (this._createPopper(), "ontouchstart" in document.documentElement && !this._parent.closest(".navbar-nav")) for (const n of [].concat(...document.body.children)) y.on(n, "mouseover", W);
                    this._element.focus(), this._element.setAttribute("aria-expanded", !0), this._menu.classList.add(Yt), this._element.classList.add(Yt), y.trigger(this._element, "shown.bs.dropdown", e)
                }
            }
        }

        hide() {
            var e;
            !a(this._element) && this._isShown() && (e = {relatedTarget: this._element}, this._completeHide(e))
        }

        dispose() {
            this._popper && this._popper.destroy(), super.dispose()
        }

        update() {
            this._inNavbar = this._detectNavbar(), this._popper && this._popper.update()
        }

        _completeHide(e) {
            var t = y.trigger(this._element, "hide.bs.dropdown", e);
            if (!t.defaultPrevented) {
                if ("ontouchstart" in document.documentElement) for (const n of [].concat(...document.body.children)) y.off(n, "mouseover", W);
                this._popper && this._popper.destroy(), this._menu.classList.remove(Yt), this._element.classList.remove(Yt), this._element.setAttribute("aria-expanded", "false"), d.removeDataAttribute(this._menu, "popper"), y.trigger(this._element, "hidden.bs.dropdown", e)
            }
        }

        _getConfig(e) {
            if ("object" != typeof (e = super._getConfig(e)).reference || s(e.reference) || "function" == typeof e.reference.getBoundingClientRect) return e;
            throw new TypeError(Xt.toUpperCase() + ': Option "reference" provided type "object" without a required "getBoundingClientRect" method.')
        }

        _createPopper() {
            if (void 0 === Vt) throw new TypeError("Bootstrap's dropdowns require Popper (https://popper.js.org)");
            let e = this._element;
            "parent" === this._config.reference ? e = this._parent : s(this._config.reference) ? e = o(this._config.reference) : "object" == typeof this._config.reference && (e = this._config.reference);
            var t = this._getPopperConfig();
            this._popper = $t(e, this._menu, t)
        }

        _isShown() {
            return this._menu.classList.contains(Yt)
        }

        _getPlacement() {
            const e = this._parent;
            if (e.classList.contains("dropend")) return tn;
            if (e.classList.contains("dropstart")) return nn;
            if (e.classList.contains("dropup-center")) return "top";
            if (e.classList.contains("dropdown-center")) return "bottom";
            var t = "end" === getComputedStyle(this._menu).getPropertyValue("--bs-position").trim();
            return e.classList.contains("dropup") ? t ? Jt : Kt : t ? en : Zt
        }

        _detectNavbar() {
            return null !== this._element.closest(".navbar")
        }

        _getOffset() {
            const t = this._config["offset"];
            return "string" == typeof t ? t.split(",").map(e => Number.parseInt(e, 10)) : "function" == typeof t ? e => t(e, this._element) : t
        }

        _getPopperConfig() {
            const e = {
                placement: this._getPlacement(),
                modifiers: [{name: "preventOverflow", options: {boundary: this._config.boundary}}, {
                    name: "offset",
                    options: {offset: this._getOffset()}
                }]
            };
            return !this._inNavbar && "static" !== this._config.display || (d.setDataAttribute(this._menu, "popper", "static"), e.modifiers = [{
                name: "applyStyles",
                enabled: !1
            }]), {...e, ..."function" == typeof this._config.popperConfig ? this._config.popperConfig(e) : this._config.popperConfig}
        }

        _selectMenuItem({key: e, target: t}) {
            const n = f.find(".dropdown-menu .dropdown-item:not(.disabled):not(:disabled)", this._menu).filter(e => i(e));
            n.length && U(n, t, e === Ut, !n.includes(t)).focus()
        }

        static jQueryInterface(t) {
            return this.each(function () {
                const e = k.getOrCreateInstance(this, t);
                if ("string" == typeof t) {
                    if (void 0 === e[t]) throw new TypeError(`No method named "${t}"`);
                    e[t]()
                }
            })
        }

        static clearMenus(e) {
            if (2 !== e.button && ("keyup" !== e.type || "Tab" === e.key)) for (const n of f.find('[data-bs-toggle="dropdown"]:not(.disabled):not(:disabled).show')) {
                const i = k.getInstance(n);
                if (i && !1 !== i._config.autoClose) {
                    const o = e.composedPath();
                    var t = o.includes(i._menu);
                    if (!(o.includes(i._element) || "inside" === i._config.autoClose && !t || "outside" === i._config.autoClose && t || i._menu.contains(e.target) && ("keyup" === e.type && "Tab" === e.key || /input|select|option|textarea|form/i.test(e.target.tagName)))) {
                        const r = {relatedTarget: i._element};
                        "click" === e.type && (r.clickEvent = e), i._completeHide(r)
                    }
                }
            }
        }

        static dataApiKeydownHandler(e) {
            var t = /input|textarea/i.test(e.target.tagName), n = "Escape" === e.key,
                i = ["ArrowUp", Ut].includes(e.key);
            if ((i || n) && (!t || n)) {
                e.preventDefault();
                const o = f.findOne(Qt, e.delegateTarget.parentNode), r = k.getOrCreateInstance(o);
                if (i) return e.stopPropagation(), r.show(), void r._selectMenuItem(e);
                r._isShown() && (e.stopPropagation(), r.hide(), o.focus())
            }
        }
    }

    y.on(document, Xe, Qt, k.dataApiKeydownHandler), y.on(document, Xe, Gt, k.dataApiKeydownHandler), y.on(document, Ve, k.clearMenus), y.on(document, "keyup.bs.dropdown.data-api", k.clearMenus), y.on(document, Ve, Qt, function (e) {
        e.preventDefault(), k.getOrCreateInstance(this).toggle()
    }), e(k);
    const sn = ".fixed-top, .fixed-bottom, .is-fixed, .sticky-top", an = ".sticky-top", ln = "padding-right",
        cn = "margin-right";

    class un {
        constructor() {
            this._element = document.body
        }

        getWidth() {
            var e = document.documentElement.clientWidth;
            return Math.abs(window.innerWidth - e)
        }

        hide() {
            const t = this.getWidth();
            this._disableOverFlow(), this._setElementAttributes(this._element, ln, e => e + t), this._setElementAttributes(sn, ln, e => e + t), this._setElementAttributes(an, cn, e => e - t)
        }

        reset() {
            this._resetElementAttributes(this._element, "overflow"), this._resetElementAttributes(this._element, ln), this._resetElementAttributes(sn, ln), this._resetElementAttributes(an, cn)
        }

        isOverflowing() {
            return 0 < this.getWidth()
        }

        _disableOverFlow() {
            this._saveInitialAttribute(this._element, "overflow"), this._element.style.overflow = "hidden"
        }

        _setElementAttributes(e, n, i) {
            const o = this.getWidth();
            this._applyManipulationCallback(e, e => {
                var t;
                e !== this._element && window.innerWidth > e.clientWidth + o || (this._saveInitialAttribute(e, n), t = window.getComputedStyle(e).getPropertyValue(n), e.style.setProperty(n, i(Number.parseFloat(t)) + "px"))
            })
        }

        _saveInitialAttribute(e, t) {
            var n = e.style.getPropertyValue(t);
            n && d.setDataAttribute(e, t, n)
        }

        _resetElementAttributes(e, n) {
            this._applyManipulationCallback(e, e => {
                var t = d.getDataAttribute(e, n);
                null === t ? e.style.removeProperty(n) : (d.removeDataAttribute(e, n), e.style.setProperty(n, t))
            })
        }

        _applyManipulationCallback(e, t) {
            if (s(e)) t(e); else for (const n of f.find(e, this._element)) t(n)
        }
    }

    const dn = "backdrop", fn = "mousedown.bs." + dn,
        hn = {className: "modal-backdrop", clickCallback: null, isAnimated: !1, isVisible: !0, rootElement: "body"},
        pn = {
            className: "string",
            clickCallback: "(function|null)",
            isAnimated: "boolean",
            isVisible: "boolean",
            rootElement: "(element|string)"
        };

    class gn extends fe {
        constructor(e) {
            super(), this._config = this._getConfig(e), this._isAppended = !1, this._element = null
        }

        static get Default() {
            return hn
        }

        static get DefaultType() {
            return pn
        }

        static get NAME() {
            return dn
        }

        show(e) {
            if (this._config.isVisible) {
                this._append();
                const t = this._getElement();
                this._config.isAnimated && z(t), t.classList.add("show"), this._emulateAnimation(() => {
                    c(e)
                })
            } else c(e)
        }

        hide(e) {
            this._config.isVisible ? (this._getElement().classList.remove("show"), this._emulateAnimation(() => {
                this.dispose(), c(e)
            })) : c(e)
        }

        dispose() {
            this._isAppended && (y.off(this._element, fn), this._element.remove(), this._isAppended = !1)
        }

        _getElement() {
            if (!this._element) {
                const e = document.createElement("div");
                e.className = this._config.className, this._config.isAnimated && e.classList.add("fade"), this._element = e
            }
            return this._element
        }

        _configAfterMerge(e) {
            return e.rootElement = o(e.rootElement), e
        }

        _append() {
            var e;
            this._isAppended || (e = this._getElement(), this._config.rootElement.append(e), y.on(e, fn, () => {
                c(this._config.clickCallback)
            }), this._isAppended = !0)
        }

        _emulateAnimation(e) {
            X(e, this._getElement(), this._config.isAnimated)
        }
    }

    const mn = ".bs.focustrap", vn = (mn, mn, "backward"), yn = {autofocus: !0, trapElement: null},
        bn = {autofocus: "boolean", trapElement: "element"};

    class _n extends fe {
        constructor(e) {
            super(), this._config = this._getConfig(e), this._isActive = !1, this._lastTabNavDirection = null
        }

        static get Default() {
            return yn
        }

        static get DefaultType() {
            return bn
        }

        static get NAME() {
            return "focustrap"
        }

        activate() {
            this._isActive || (this._config.autofocus && this._config.trapElement.focus(), y.off(document, mn), y.on(document, "focusin.bs.focustrap", e => this._handleFocusin(e)), y.on(document, "keydown.tab.bs.focustrap", e => this._handleKeydown(e)), this._isActive = !0)
        }

        deactivate() {
            this._isActive && (this._isActive = !1, y.off(document, mn))
        }

        _handleFocusin(e) {
            const t = this._config["trapElement"];
            if (e.target !== document && e.target !== t && !t.contains(e.target)) {
                const n = f.focusableChildren(t);
                (0 === n.length ? t : this._lastTabNavDirection === vn ? n[n.length - 1] : n[0]).focus()
            }
        }

        _handleKeydown(e) {
            "Tab" === e.key && (this._lastTabNavDirection = e.shiftKey ? vn : "forward")
        }
    }

    const C = ".bs.modal";
    C, C;
    const wn = "hidden" + C, xn = "show" + C;
    C, C, C, C;
    C;
    const Tn = "modal-open", kn = "modal-static";
    const Cn = {backdrop: !0, focus: !0, keyboard: !0},
        An = {backdrop: "(boolean|string)", focus: "boolean", keyboard: "boolean"};

    class En extends t {
        constructor(e, t) {
            super(e, t), this._dialog = f.findOne(".modal-dialog", this._element), this._backdrop = this._initializeBackDrop(), this._focustrap = this._initializeFocusTrap(), this._isShown = !1, this._isTransitioning = !1, this._scrollBar = new un, this._addEventListeners()
        }

        static get Default() {
            return Cn
        }

        static get DefaultType() {
            return An
        }

        static get NAME() {
            return "modal"
        }

        toggle(e) {
            return this._isShown ? this.hide() : this.show(e)
        }

        show(e) {
            this._isShown || this._isTransitioning || y.trigger(this._element, xn, {relatedTarget: e}).defaultPrevented || (this._isShown = !0, this._isTransitioning = !0, this._scrollBar.hide(), document.body.classList.add(Tn), this._adjustDialog(), this._backdrop.show(() => this._showElement(e)))
        }

        hide() {
            !this._isShown || this._isTransitioning || y.trigger(this._element, "hide.bs.modal").defaultPrevented || (this._isShown = !1, this._isTransitioning = !0, this._focustrap.deactivate(), this._element.classList.remove("show"), this._queueCallback(() => this._hideModal(), this._element, this._isAnimated()))
        }

        dispose() {
            for (const e of [window, this._dialog]) y.off(e, C);
            this._backdrop.dispose(), this._focustrap.deactivate(), super.dispose()
        }

        handleUpdate() {
            this._adjustDialog()
        }

        _initializeBackDrop() {
            return new gn({isVisible: Boolean(this._config.backdrop), isAnimated: this._isAnimated()})
        }

        _initializeFocusTrap() {
            return new _n({trapElement: this._element})
        }

        _showElement(e) {
            document.body.contains(this._element) || document.body.append(this._element), this._element.style.display = "block", this._element.removeAttribute("aria-hidden"), this._element.setAttribute("aria-modal", !0), this._element.setAttribute("role", "dialog"), this._element.scrollTop = 0;
            const t = f.findOne(".modal-body", this._dialog);
            t && (t.scrollTop = 0), z(this._element), this._element.classList.add("show");
            this._queueCallback(() => {
                this._config.focus && this._focustrap.activate(), this._isTransitioning = !1, y.trigger(this._element, "shown.bs.modal", {relatedTarget: e})
            }, this._dialog, this._isAnimated())
        }

        _addEventListeners() {
            y.on(this._element, "keydown.dismiss.bs.modal", e => {
                if ("Escape" === e.key) return this._config.keyboard ? (e.preventDefault(), void this.hide()) : void this._triggerBackdropTransition()
            }), y.on(window, "resize.bs.modal", () => {
                this._isShown && !this._isTransitioning && this._adjustDialog()
            }), y.on(this._element, "mousedown.dismiss.bs.modal", e => {
                e.target === e.currentTarget && ("static" === this._config.backdrop ? this._triggerBackdropTransition() : this._config.backdrop && this.hide())
            })
        }

        _hideModal() {
            this._element.style.display = "none", this._element.setAttribute("aria-hidden", !0), this._element.removeAttribute("aria-modal"), this._element.removeAttribute("role"), this._isTransitioning = !1, this._backdrop.hide(() => {
                document.body.classList.remove(Tn), this._resetAdjustments(), this._scrollBar.reset(), y.trigger(this._element, wn)
            })
        }

        _isAnimated() {
            return this._element.classList.contains("fade")
        }

        _triggerBackdropTransition() {
            var e = y.trigger(this._element, "hidePrevented.bs.modal");
            if (!e.defaultPrevented) {
                e = this._element.scrollHeight > document.documentElement.clientHeight;
                const t = this._element.style.overflowY;
                "hidden" === t || this._element.classList.contains(kn) || (e || (this._element.style.overflowY = "hidden"), this._element.classList.add(kn), this._queueCallback(() => {
                    this._element.classList.remove(kn), this._queueCallback(() => {
                        this._element.style.overflowY = t
                    }, this._dialog)
                }, this._dialog), this._element.focus())
            }
        }

        _adjustDialog() {
            var e, t = this._element.scrollHeight > document.documentElement.clientHeight,
                n = this._scrollBar.getWidth(), i = 0 < n;
            i && !t && (e = l() ? "paddingLeft" : "paddingRight", this._element.style[e] = n + "px"), !i && t && (e = l() ? "paddingRight" : "paddingLeft", this._element.style[e] = n + "px")
        }

        _resetAdjustments() {
            this._element.style.paddingLeft = "", this._element.style.paddingRight = ""
        }

        static jQueryInterface(t, n) {
            return this.each(function () {
                const e = En.getOrCreateInstance(this, t);
                if ("string" == typeof t) {
                    if (void 0 === e[t]) throw new TypeError(`No method named "${t}"`);
                    e[t](n)
                }
            })
        }
    }

    y.on(document, "click.bs.modal.data-api", '[data-bs-toggle="modal"]', function (e) {
        const t = r(this);
        ["A", "AREA"].includes(this.tagName) && e.preventDefault(), y.one(t, xn, e => {
            e.defaultPrevented || y.one(t, wn, () => {
                i(this) && this.focus()
            })
        });
        e = f.findOne(".modal.show");
        e && En.getInstance(e).hide();
        const n = En.getOrCreateInstance(t);
        n.toggle(this)
    }), he(En), e(En);
    Ue = ".bs.offcanvas";
    const Sn = "showing", Dn = ".offcanvas.show", Ln = "hidePrevented" + Ue, On = "hidden" + Ue;
    const Nn = {backdrop: !0, keyboard: !0, scroll: !1},
        jn = {backdrop: "(boolean|string)", keyboard: "boolean", scroll: "boolean"};

    class I extends t {
        constructor(e, t) {
            super(e, t), this._isShown = !1, this._backdrop = this._initializeBackDrop(), this._focustrap = this._initializeFocusTrap(), this._addEventListeners()
        }

        static get Default() {
            return Nn
        }

        static get DefaultType() {
            return jn
        }

        static get NAME() {
            return "offcanvas"
        }

        toggle(e) {
            return this._isShown ? this.hide() : this.show(e)
        }

        show(e) {
            this._isShown || y.trigger(this._element, "show.bs.offcanvas", {relatedTarget: e}).defaultPrevented || (this._isShown = !0, this._backdrop.show(), this._config.scroll || (new un).hide(), this._element.setAttribute("aria-modal", !0), this._element.setAttribute("role", "dialog"), this._element.classList.add(Sn), this._queueCallback(() => {
                this._config.scroll && !this._config.backdrop || this._focustrap.activate(), this._element.classList.add("show"), this._element.classList.remove(Sn), y.trigger(this._element, "shown.bs.offcanvas", {relatedTarget: e})
            }, this._element, !0))
        }

        hide() {
            this._isShown && !y.trigger(this._element, "hide.bs.offcanvas").defaultPrevented && (this._focustrap.deactivate(), this._element.blur(), this._isShown = !1, this._element.classList.add("hiding"), this._backdrop.hide(), this._queueCallback(() => {
                this._element.classList.remove("show", "hiding"), this._element.removeAttribute("aria-modal"), this._element.removeAttribute("role"), this._config.scroll || (new un).reset(), y.trigger(this._element, On)
            }, this._element, !0))
        }

        dispose() {
            this._backdrop.dispose(), this._focustrap.deactivate(), super.dispose()
        }

        _initializeBackDrop() {
            var e = Boolean(this._config.backdrop);
            return new gn({
                className: "offcanvas-backdrop",
                isVisible: e,
                isAnimated: !0,
                rootElement: this._element.parentNode,
                clickCallback: e ? () => {
                    "static" === this._config.backdrop ? y.trigger(this._element, Ln) : this.hide()
                } : null
            })
        }

        _initializeFocusTrap() {
            return new _n({trapElement: this._element})
        }

        _addEventListeners() {
            y.on(this._element, "keydown.dismiss.bs.offcanvas", e => {
                "Escape" === e.key && (this._config.keyboard ? this.hide() : y.trigger(this._element, Ln))
            })
        }

        static jQueryInterface(t) {
            return this.each(function () {
                const e = I.getOrCreateInstance(this, t);
                if ("string" == typeof t) {
                    if (void 0 === e[t] || t.startsWith("_") || "constructor" === t) throw new TypeError(`No method named "${t}"`);
                    e[t](this)
                }
            })
        }
    }

    y.on(document, "click.bs.offcanvas.data-api", '[data-bs-toggle="offcanvas"]', function (e) {
        var t = r(this);
        if (["A", "AREA"].includes(this.tagName) && e.preventDefault(), !a(this)) {
            y.one(t, On, () => {
                i(this) && this.focus()
            });
            e = f.findOne(Dn);
            e && e !== t && I.getInstance(e).hide();
            const n = I.getOrCreateInstance(t);
            n.toggle(this)
        }
    }), y.on(window, "load.bs.offcanvas.data-api", () => {
        for (const e of f.find(Dn)) I.getOrCreateInstance(e).show()
    }), y.on(window, "resize.bs.offcanvas", () => {
        for (const e of f.find("[aria-modal][class*=show][class*=offcanvas-]")) "fixed" !== getComputedStyle(e).position && I.getOrCreateInstance(e).hide()
    }), he(I), e(I);
    const In = new Set(["background", "cite", "href", "itemtype", "longdesc", "poster", "src", "xlink:href"]);
    const Pn = /^(?:(?:https?|mailto|ftp|tel|file|sms):|[^#&/:?]*(?:[#/?]|$))/i,
        Mn = /^data:(?:image\/(?:bmp|gif|jpeg|jpg|png|tiff|webp)|video\/(?:mpeg|mp4|ogg|webm)|audio\/(?:mp3|oga|ogg|opus));base64,[\d+/a-z]+=*$/i;
    Ye = {
        "*": ["class", "dir", "id", "lang", "role", /^aria-[\w-]*$/i],
        a: ["target", "href", "title", "rel"],
        area: [],
        b: [],
        br: [],
        col: [],
        code: [],
        div: [],
        em: [],
        hr: [],
        h1: [],
        h2: [],
        h3: [],
        h4: [],
        h5: [],
        h6: [],
        i: [],
        img: ["src", "srcset", "alt", "title", "width", "height"],
        li: [],
        ol: [],
        p: [],
        pre: [],
        s: [],
        small: [],
        span: [],
        sub: [],
        sup: [],
        strong: [],
        u: [],
        ul: []
    };

    function Hn(e, t, n) {
        if (!e.length) return e;
        if (n && "function" == typeof n) return n(e);
        const i = new window.DOMParser, o = i.parseFromString(e, "text/html");
        for (const l of [].concat(...o.body.querySelectorAll("*"))) {
            var r = l.nodeName.toLowerCase();
            if (Object.keys(t).includes(r)) {
                var s = [].concat(...l.attributes), a = [].concat(t["*"] || [], t[r] || []);
                for (const c of s) ((e, t) => {
                    const n = e.nodeName.toLowerCase();
                    return t.includes(n) ? !In.has(n) || Boolean(Pn.test(e.nodeValue) || Mn.test(e.nodeValue)) : t.filter(e => e instanceof RegExp).some(e => e.test(n))
                })(c, a) || l.removeAttribute(c.nodeName)
            } else l.remove()
        }
        return o.body.innerHTML
    }

    const qn = {
        allowList: Ye,
        content: {},
        extraClass: "",
        html: !1,
        sanitize: !0,
        sanitizeFn: null,
        template: "<div></div>"
    }, Rn = {
        allowList: "object",
        content: "object",
        extraClass: "(string|function)",
        html: "boolean",
        sanitize: "boolean",
        sanitizeFn: "(null|function)",
        template: "string"
    }, Bn = {entry: "(string|element|function|null)", selector: "(string|element)"};

    class Fn extends fe {
        constructor(e) {
            super(), this._config = this._getConfig(e)
        }

        static get Default() {
            return qn
        }

        static get DefaultType() {
            return Rn
        }

        static get NAME() {
            return "TemplateFactory"
        }

        getContent() {
            return Object.values(this._config.content).map(e => this._resolvePossibleFunction(e)).filter(Boolean)
        }

        hasContent() {
            return 0 < this.getContent().length
        }

        changeContent(e) {
            return this._checkContent(e), this._config.content = {...this._config.content, ...e}, this
        }

        toHtml() {
            const e = document.createElement("div");
            e.innerHTML = this._maybeSanitize(this._config.template);
            for (var [t, n] of Object.entries(this._config.content)) this._setContent(e, n, t);
            const i = e.children[0], o = this._resolvePossibleFunction(this._config.extraClass);
            return o && i.classList.add(...o.split(" ")), i
        }

        _typeCheckConfig(e) {
            super._typeCheckConfig(e), this._checkContent(e.content)
        }

        _checkContent(e) {
            for (var [t, n] of Object.entries(e)) super._typeCheckConfig({selector: t, entry: n}, Bn)
        }

        _setContent(e, t, n) {
            const i = f.findOne(n, e);
            i && ((t = this._resolvePossibleFunction(t)) ? s(t) ? this._putElementInTemplate(o(t), i) : this._config.html ? i.innerHTML = this._maybeSanitize(t) : i.textContent = t : i.remove())
        }

        _maybeSanitize(e) {
            return this._config.sanitize ? Hn(e, this._config.allowList, this._config.sanitizeFn) : e
        }

        _resolvePossibleFunction(e) {
            return "function" == typeof e ? e(this) : e
        }

        _putElementInTemplate(e, t) {
            if (this._config.html) return t.innerHTML = "", void t.append(e);
            t.textContent = e.textContent
        }
    }

    const Wn = new Set(["sanitize", "allowList", "sanitizeFn"]), zn = "fade";
    const $n = "show", Vn = "hide.bs.modal", Xn = "hover", Un = "focus",
        Yn = {AUTO: "auto", TOP: "top", RIGHT: l() ? "left" : "right", BOTTOM: "bottom", LEFT: l() ? "right" : "left"},
        Qn = {
            allowList: Ye,
            animation: !0,
            boundary: "clippingParents",
            container: !1,
            customClass: "",
            delay: 0,
            fallbackPlacements: ["top", "right", "bottom", "left"],
            html: !1,
            offset: [0, 0],
            placement: "top",
            popperConfig: null,
            sanitize: !0,
            sanitizeFn: null,
            selector: !1,
            template: '<div class="tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
            title: "",
            trigger: "hover focus"
        }, Gn = {
            allowList: "object",
            animation: "boolean",
            boundary: "(string|element)",
            container: "(string|element|boolean)",
            customClass: "(string|function)",
            delay: "(number|object)",
            fallbackPlacements: "array",
            html: "boolean",
            offset: "(array|string|function)",
            placement: "(string|function)",
            popperConfig: "(null|object|function)",
            sanitize: "boolean",
            sanitizeFn: "(null|function)",
            selector: "(string|boolean)",
            template: "string",
            title: "(string|element|function)",
            trigger: "string"
        };

    class Kn extends t {
        constructor(e, t) {
            if (void 0 === Vt) throw new TypeError("Bootstrap's tooltips require Popper (https://popper.js.org)");
            super(e, t), this._isEnabled = !0, this._timeout = 0, this._isHovered = !1, this._activeTrigger = {}, this._popper = null, this._templateFactory = null, this._newContent = null, this.tip = null, this._setListeners()
        }

        static get Default() {
            return Qn
        }

        static get DefaultType() {
            return Gn
        }

        static get NAME() {
            return "tooltip"
        }

        enable() {
            this._isEnabled = !0
        }

        disable() {
            this._isEnabled = !1
        }

        toggleEnabled() {
            this._isEnabled = !this._isEnabled
        }

        toggle(e) {
            if (this._isEnabled) {
                if (e) {
                    const t = this._initializeOnDelegatedTarget(e);
                    return t._activeTrigger.click = !t._activeTrigger.click, void (t._isWithActiveTrigger() ? t._enter() : t._leave())
                }
                this._isShown() ? this._leave() : this._enter()
            }
        }

        dispose() {
            clearTimeout(this._timeout), y.off(this._element.closest(".modal"), Vn, this._hideModalHandler), this.tip && this.tip.remove(), this._disposePopper(), super.dispose()
        }

        show() {
            if ("none" === this._element.style.display) throw new Error("Please use show on visible elements");
            if (this._isWithContent() && this._isEnabled) {
                var e = y.trigger(this._element, this.constructor.eventName("show"));
                const n = F(this._element);
                var t = (n || this._element.ownerDocument.documentElement).contains(this._element);
                if (!e.defaultPrevented && t) {
                    this.tip && (this.tip.remove(), this.tip = null);
                    const i = this._getTipElement(),
                        o = (this._element.setAttribute("aria-describedby", i.getAttribute("id")), this._config)["container"];
                    if (this._element.ownerDocument.documentElement.contains(this.tip) || (o.append(i), y.trigger(this._element, this.constructor.eventName("inserted"))), this._popper ? this._popper.update() : this._popper = this._createPopper(i), i.classList.add($n), "ontouchstart" in document.documentElement) for (const r of [].concat(...document.body.children)) y.on(r, "mouseover", W);
                    this._queueCallback(() => {
                        var e = this._isHovered;
                        this._isHovered = !1, y.trigger(this._element, this.constructor.eventName("shown")), e && this._leave()
                    }, this.tip, this._isAnimated())
                }
            }
        }

        hide() {
            if (this._isShown()) {
                var e = y.trigger(this._element, this.constructor.eventName("hide"));
                if (!e.defaultPrevented) {
                    const t = this._getTipElement();
                    if (t.classList.remove($n), "ontouchstart" in document.documentElement) for (const n of [].concat(...document.body.children)) y.off(n, "mouseover", W);
                    this._activeTrigger.click = !1, this._activeTrigger[Un] = !1, this._activeTrigger[Xn] = !1, this._isHovered = !1;
                    this._queueCallback(() => {
                        this._isWithActiveTrigger() || (this._isHovered || t.remove(), this._element.removeAttribute("aria-describedby"), y.trigger(this._element, this.constructor.eventName("hidden")), this._disposePopper())
                    }, this.tip, this._isAnimated())
                }
            }
        }

        update() {
            this._popper && this._popper.update()
        }

        _isWithContent() {
            return Boolean(this._getTitle())
        }

        _getTipElement() {
            return this.tip || (this.tip = this._createTipElement(this._newContent || this._getContentForTemplate())), this.tip
        }

        _createTipElement(e) {
            const t = this._getTemplateFactory(e).toHtml();
            if (!t) return null;
            t.classList.remove(zn, $n), t.classList.add(`bs-${this.constructor.NAME}-auto`);
            e = (e => {
                for (; e += Math.floor(1e6 * Math.random()), document.getElementById(e);) ;
                return e
            })(this.constructor.NAME).toString();
            return t.setAttribute("id", e), this._isAnimated() && t.classList.add(zn), t
        }

        setContent(e) {
            this._newContent = e, this._isShown() && (this._disposePopper(), this.show())
        }

        _getTemplateFactory(e) {
            return this._templateFactory ? this._templateFactory.changeContent(e) : this._templateFactory = new Fn({
                ...this._config,
                content: e,
                extraClass: this._resolvePossibleFunction(this._config.customClass)
            }), this._templateFactory
        }

        _getContentForTemplate() {
            return {".tooltip-inner": this._getTitle()}
        }

        _getTitle() {
            return this._resolvePossibleFunction(this._config.title) || this._config.originalTitle
        }

        _initializeOnDelegatedTarget(e) {
            return this.constructor.getOrCreateInstance(e.delegateTarget, this._getDelegateConfig())
        }

        _isAnimated() {
            return this._config.animation || this.tip && this.tip.classList.contains(zn)
        }

        _isShown() {
            return this.tip && this.tip.classList.contains($n)
        }

        _createPopper(e) {
            const t = "function" == typeof this._config.placement ? this._config.placement.call(this, e, this._element) : this._config.placement;
            var n = Yn[t.toUpperCase()];
            return $t(this._element, e, this._getPopperConfig(n))
        }

        _getOffset() {
            const t = this._config["offset"];
            return "string" == typeof t ? t.split(",").map(e => Number.parseInt(e, 10)) : "function" == typeof t ? e => t(e, this._element) : t
        }

        _resolvePossibleFunction(e) {
            return "function" == typeof e ? e.call(this._element) : e
        }

        _getPopperConfig(e) {
            e = {
                placement: e,
                modifiers: [{
                    name: "flip",
                    options: {fallbackPlacements: this._config.fallbackPlacements}
                }, {name: "offset", options: {offset: this._getOffset()}}, {
                    name: "preventOverflow",
                    options: {boundary: this._config.boundary}
                }, {name: "arrow", options: {element: `.${this.constructor.NAME}-arrow`}}, {
                    name: "preSetPlacement",
                    enabled: !0,
                    phase: "beforeMain",
                    fn: e => {
                        this._getTipElement().setAttribute("data-popper-placement", e.state.placement)
                    }
                }]
            };
            return {...e, ..."function" == typeof this._config.popperConfig ? this._config.popperConfig(e) : this._config.popperConfig}
        }

        _setListeners() {
            var e, t;
            for (const n of this._config.trigger.split(" ")) "click" === n ? y.on(this._element, this.constructor.eventName("click"), this._config.selector, e => this.toggle(e)) : "manual" !== n && (e = n === Xn ? this.constructor.eventName("mouseenter") : this.constructor.eventName("focusin"), t = n === Xn ? this.constructor.eventName("mouseleave") : this.constructor.eventName("focusout"), y.on(this._element, e, this._config.selector, e => {
                const t = this._initializeOnDelegatedTarget(e);
                t._activeTrigger["focusin" === e.type ? Un : Xn] = !0, t._enter()
            }), y.on(this._element, t, this._config.selector, e => {
                const t = this._initializeOnDelegatedTarget(e);
                t._activeTrigger["focusout" === e.type ? Un : Xn] = t._element.contains(e.relatedTarget), t._leave()
            }));
            this._hideModalHandler = () => {
                this._element && this.hide()
            }, y.on(this._element.closest(".modal"), Vn, this._hideModalHandler), this._config.selector ? this._config = {
                ...this._config,
                trigger: "manual",
                selector: ""
            } : this._fixTitle()
        }

        _fixTitle() {
            var e = this._config.originalTitle;
            e && (this._element.getAttribute("aria-label") || this._element.textContent.trim() || this._element.setAttribute("aria-label", e), this._element.removeAttribute("title"))
        }

        _enter() {
            this._isShown() || this._isHovered ? this._isHovered = !0 : (this._isHovered = !0, this._setTimeout(() => {
                this._isHovered && this.show()
            }, this._config.delay.show))
        }

        _leave() {
            this._isWithActiveTrigger() || (this._isHovered = !1, this._setTimeout(() => {
                this._isHovered || this.hide()
            }, this._config.delay.hide))
        }

        _setTimeout(e, t) {
            clearTimeout(this._timeout), this._timeout = setTimeout(e, t)
        }

        _isWithActiveTrigger() {
            return Object.values(this._activeTrigger).includes(!0)
        }

        _getConfig(e) {
            const t = d.getDataAttributes(this._element);
            for (const n of Object.keys(t)) Wn.has(n) && delete t[n];
            return e = {...t, ..."object" == typeof e && e ? e : {}}, e = this._mergeConfigObj(e), e = this._configAfterMerge(e), this._typeCheckConfig(e), e
        }

        _configAfterMerge(e) {
            return e.container = !1 === e.container ? document.body : o(e.container), "number" == typeof e.delay && (e.delay = {
                show: e.delay,
                hide: e.delay
            }), e.originalTitle = this._element.getAttribute("title") || "", "number" == typeof e.title && (e.title = e.title.toString()), "number" == typeof e.content && (e.content = e.content.toString()), e
        }

        _getDelegateConfig() {
            const e = {};
            for (const t in this._config) this.constructor.Default[t] !== this._config[t] && (e[t] = this._config[t]);
            return e
        }

        _disposePopper() {
            this._popper && (this._popper.destroy(), this._popper = null)
        }

        static jQueryInterface(t) {
            return this.each(function () {
                const e = Kn.getOrCreateInstance(this, t);
                if ("string" == typeof t) {
                    if (void 0 === e[t]) throw new TypeError(`No method named "${t}"`);
                    e[t]()
                }
            })
        }
    }

    e(Kn);
    const Jn = {
        ...Kn.Default,
        content: "",
        offset: [0, 8],
        placement: "right",
        template: '<div class="popover" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
        trigger: "click"
    }, Zn = {...Kn.DefaultType, content: "(null|string|element|function)"};

    class ei extends Kn {
        static get Default() {
            return Jn
        }

        static get DefaultType() {
            return Zn
        }

        static get NAME() {
            return "popover"
        }

        _isWithContent() {
            return this._getTitle() || this._getContent()
        }

        _getContentForTemplate() {
            return {".popover-header": this._getTitle(), ".popover-body": this._getContent()}
        }

        _getContent() {
            return this._resolvePossibleFunction(this._config.content)
        }

        static jQueryInterface(t) {
            return this.each(function () {
                const e = ei.getOrCreateInstance(this, t);
                if ("string" == typeof t) {
                    if (void 0 === e[t]) throw new TypeError(`No method named "${t}"`);
                    e[t]()
                }
            })
        }
    }

    e(ei);
    Je = ".bs.scrollspy";
    const ti = "click" + Je;
    const ni = "active", ii = "[href]";
    const oi = {offset: null, rootMargin: "0px 0px -25%", smoothScroll: !1, target: null},
        ri = {offset: "(number|null)", rootMargin: "string", smoothScroll: "boolean", target: "element"};

    class si extends t {
        constructor(e, t) {
            super(e, t), this._targetLinks = new Map, this._observableSections = new Map, this._rootElement = "visible" === getComputedStyle(this._element).overflowY ? null : this._element, this._activeTarget = null, this._observer = null, this._previousScrollData = {
                visibleEntryTop: 0,
                parentScrollTop: 0
            }, this.refresh()
        }

        static get Default() {
            return oi
        }

        static get DefaultType() {
            return ri
        }

        static get NAME() {
            return "scrollspy"
        }

        refresh() {
            this._initializeTargetsAndObservables(), this._maybeEnableSmoothScroll(), this._observer ? this._observer.disconnect() : this._observer = this._getNewObserver();
            for (const e of this._observableSections.values()) this._observer.observe(e)
        }

        dispose() {
            this._observer.disconnect(), super.dispose()
        }

        _configAfterMerge(e) {
            return e.target = o(e.target) || document.body, e
        }

        _maybeEnableSmoothScroll() {
            this._config.smoothScroll && (y.off(this._config.target, ti), y.on(this._config.target, ti, ii, e => {
                var t = this._observableSections.get(e.target.hash);
                if (t) {
                    e.preventDefault();
                    const n = this._rootElement || window;
                    e = t.offsetTop - this._element.offsetTop;
                    n.scrollTo ? n.scrollTo({top: e, behavior: "smooth"}) : n.scrollTop = e
                }
            }))
        }

        _getNewObserver() {
            var e = {root: this._rootElement, threshold: [.1, .5, 1], rootMargin: this._getRootMargin()};
            return new IntersectionObserver(e => this._observerCallback(e), e)
        }

        _observerCallback(e) {
            const t = e => this._targetLinks.get("#" + e.target.id);
            var n = e => {
                    this._previousScrollData.visibleEntryTop = e.target.offsetTop, this._process(t(e))
                }, i = (this._rootElement || document.documentElement).scrollTop,
                o = i >= this._previousScrollData.parentScrollTop;
            this._previousScrollData.parentScrollTop = i;
            for (const s of e) if (s.isIntersecting) {
                var r = s.target.offsetTop >= this._previousScrollData.visibleEntryTop;
                if (o && r) {
                    if (n(s), i) continue;
                    return
                }
                o || r || n(s)
            } else this._activeTarget = null, this._clearActiveClass(t(s))
        }

        _getRootMargin() {
            return this._config.offset ? this._config.offset + "px 0px -30%" : this._config.rootMargin
        }

        _initializeTargetsAndObservables() {
            var e;
            this._targetLinks = new Map, this._observableSections = new Map;
            for (const t of f.find(ii, this._config.target)) t.hash && !a(t) && (e = f.findOne(t.hash, this._element), i(e) && (this._targetLinks.set(t.hash, t), this._observableSections.set(t.hash, e)))
        }

        _process(e) {
            this._activeTarget !== e && (this._clearActiveClass(this._config.target), (this._activeTarget = e).classList.add(ni), this._activateParents(e), y.trigger(this._element, "activate.bs.scrollspy", {relatedTarget: e}))
        }

        _activateParents(e) {
            if (e.classList.contains("dropdown-item")) f.findOne(".dropdown-toggle", e.closest(".dropdown")).classList.add(ni); else for (const t of f.parents(e, ".nav, .list-group")) for (const n of f.prev(t, ".nav-link, .nav-item > .nav-link, .list-group-item")) n.classList.add(ni)
        }

        _clearActiveClass(e) {
            e.classList.remove(ni);
            for (const t of f.find(ii + "." + ni, e)) t.classList.remove(ni)
        }

        static jQueryInterface(t) {
            return this.each(function () {
                const e = si.getOrCreateInstance(this, t);
                if ("string" == typeof t) {
                    if (void 0 === e[t] || t.startsWith("_") || "constructor" === t) throw new TypeError(`No method named "${t}"`);
                    e[t]()
                }
            })
        }
    }

    y.on(window, "load.bs.scrollspy.data-api", () => {
        for (const e of f.find('[data-bs-spy="scroll"]')) si.getOrCreateInstance(e)
    }), e(si);
    const ai = "ArrowRight", li = "ArrowDown", P = "active", ci = "show";
    ft = '[data-bs-toggle="tab"], [data-bs-toggle="pill"], [data-bs-toggle="list"]';
    const ui = '.nav-link:not(.dropdown-toggle), .list-group-item:not(.dropdown-toggle), [role="tab"]:not(.dropdown-toggle), ' + ft;
    P, P, P;

    class di extends t {
        constructor(e) {
            super(e), this._parent = this._element.closest('.list-group, .nav, [role="tablist"]'), this._parent && (this._setInitialAttributes(this._parent, this._getChildren()), y.on(this._element, "keydown.bs.tab", e => this._keydown(e)))
        }

        static get NAME() {
            return "tab"
        }

        show() {
            var e, t, n = this._element;
            this._elemIsActive(n) || (t = (e = this._getActiveElem()) ? y.trigger(e, "hide.bs.tab", {relatedTarget: n}) : null, y.trigger(n, "show.bs.tab", {relatedTarget: e}).defaultPrevented || t && t.defaultPrevented || (this._deactivate(e, n), this._activate(n, e)))
        }

        _activate(e, t) {
            e && (e.classList.add(P), this._activate(r(e)), this._queueCallback(() => {
                "tab" !== e.getAttribute("role") ? e.classList.add(ci) : (e.focus(), e.removeAttribute("tabindex"), e.setAttribute("aria-selected", !0), this._toggleDropDown(e, !0), y.trigger(e, "shown.bs.tab", {relatedTarget: t}))
            }, e, e.classList.contains("fade")))
        }

        _deactivate(e, t) {
            e && (e.classList.remove(P), e.blur(), this._deactivate(r(e)), this._queueCallback(() => {
                "tab" !== e.getAttribute("role") ? e.classList.remove(ci) : (e.setAttribute("aria-selected", !1), e.setAttribute("tabindex", "-1"), this._toggleDropDown(e, !1), y.trigger(e, "hidden.bs.tab", {relatedTarget: t}))
            }, e, e.classList.contains("fade")))
        }

        _keydown(e) {
            var t;
            ["ArrowLeft", ai, "ArrowUp", li].includes(e.key) && (e.stopPropagation(), e.preventDefault(), t = [ai, li].includes(e.key), (e = U(this._getChildren().filter(e => !a(e)), e.target, t, !0)) && di.getOrCreateInstance(e).show())
        }

        _getChildren() {
            return f.find(ui, this._parent)
        }

        _getActiveElem() {
            return this._getChildren().find(e => this._elemIsActive(e)) || null
        }

        _setInitialAttributes(e, t) {
            this._setAttributeIfNotExists(e, "role", "tablist");
            for (const n of t) this._setInitialAttributesOnChild(n)
        }

        _setInitialAttributesOnChild(e) {
            e = this._getInnerElement(e);
            var t = this._elemIsActive(e), n = this._getOuterElement(e);
            e.setAttribute("aria-selected", t), n !== e && this._setAttributeIfNotExists(n, "role", "presentation"), t || e.setAttribute("tabindex", "-1"), this._setAttributeIfNotExists(e, "role", "tab"), this._setInitialAttributesOnTargetPanel(e)
        }

        _setInitialAttributesOnTargetPanel(e) {
            var t = r(e);
            t && (this._setAttributeIfNotExists(t, "role", "tabpanel"), e.id && this._setAttributeIfNotExists(t, "aria-labelledby", "#" + e.id))
        }

        _toggleDropDown(e, i) {
            const o = this._getOuterElement(e);
            o.classList.contains("dropdown") && ((e = (e, t) => {
                const n = f.findOne(e, o);
                n && n.classList.toggle(t, i)
            })(".dropdown-toggle", P), e(".dropdown-menu", ci), e(".dropdown-item", P), o.setAttribute("aria-expanded", i))
        }

        _setAttributeIfNotExists(e, t, n) {
            e.hasAttribute(t) || e.setAttribute(t, n)
        }

        _elemIsActive(e) {
            return e.classList.contains(P)
        }

        _getInnerElement(e) {
            return e.matches(ui) ? e : f.findOne(ui, e)
        }

        _getOuterElement(e) {
            return e.closest(".nav-item, .list-group-item") || e
        }

        static jQueryInterface(t) {
            return this.each(function () {
                const e = di.getOrCreateInstance(this);
                if ("string" == typeof t) {
                    if (void 0 === e[t] || t.startsWith("_") || "constructor" === t) throw new TypeError(`No method named "${t}"`);
                    e[t]()
                }
            })
        }
    }

    y.on(document, "click.bs.tab", ft, function (e) {
        ["A", "AREA"].includes(this.tagName) && e.preventDefault(), a(this) || di.getOrCreateInstance(this).show()
    }), y.on(window, "load.bs.tab", () => {
        for (const e of f.find('.active[data-bs-toggle="tab"], .active[data-bs-toggle="pill"], .active[data-bs-toggle="list"]')) di.getOrCreateInstance(e)
    }), e(di);
    const fi = "show", hi = "showing", pi = {animation: "boolean", autohide: "boolean", delay: "number"},
        gi = {animation: !0, autohide: !0, delay: 5e3};

    class mi extends t {
        constructor(e, t) {
            super(e, t), this._timeout = null, this._hasMouseInteraction = !1, this._hasKeyboardInteraction = !1, this._setListeners()
        }

        static get Default() {
            return gi
        }

        static get DefaultType() {
            return pi
        }

        static get NAME() {
            return "toast"
        }

        show() {
            y.trigger(this._element, "show.bs.toast").defaultPrevented || (this._clearTimeout(), this._config.animation && this._element.classList.add("fade"), this._element.classList.remove("hide"), z(this._element), this._element.classList.add(fi, hi), this._queueCallback(() => {
                this._element.classList.remove(hi), y.trigger(this._element, "shown.bs.toast"), this._maybeScheduleHide()
            }, this._element, this._config.animation))
        }

        hide() {
            this.isShown() && !y.trigger(this._element, "hide.bs.toast").defaultPrevented && (this._element.classList.add(hi), this._queueCallback(() => {
                this._element.classList.add("hide"), this._element.classList.remove(hi, fi), y.trigger(this._element, "hidden.bs.toast")
            }, this._element, this._config.animation))
        }

        dispose() {
            this._clearTimeout(), this.isShown() && this._element.classList.remove(fi), super.dispose()
        }

        isShown() {
            return this._element.classList.contains(fi)
        }

        _maybeScheduleHide() {
            !this._config.autohide || this._hasMouseInteraction || this._hasKeyboardInteraction || (this._timeout = setTimeout(() => {
                this.hide()
            }, this._config.delay))
        }

        _onInteraction(e, t) {
            switch (e.type) {
                case"mouseover":
                case"mouseout":
                    this._hasMouseInteraction = t;
                    break;
                case"focusin":
                case"focusout":
                    this._hasKeyboardInteraction = t
            }
            t ? this._clearTimeout() : (e = e.relatedTarget, this._element === e || this._element.contains(e) || this._maybeScheduleHide())
        }

        _setListeners() {
            y.on(this._element, "mouseover.bs.toast", e => this._onInteraction(e, !0)), y.on(this._element, "mouseout.bs.toast", e => this._onInteraction(e, !1)), y.on(this._element, "focusin.bs.toast", e => this._onInteraction(e, !0)), y.on(this._element, "focusout.bs.toast", e => this._onInteraction(e, !1))
        }

        _clearTimeout() {
            clearTimeout(this._timeout), this._timeout = null
        }

        static jQueryInterface(t) {
            return this.each(function () {
                const e = mi.getOrCreateInstance(this, t);
                if ("string" == typeof t) {
                    if (void 0 === e[t]) throw new TypeError(`No method named "${t}"`);
                    e[t](this)
                }
            })
        }
    }

    return he(mi), e(mi), {
        Alert: pe,
        Button: me,
        Carousel: Le,
        Collapse: Me,
        Dropdown: k,
        Modal: En,
        Offcanvas: I,
        Popover: ei,
        ScrollSpy: si,
        Tab: di,
        Toast: mi,
        Tooltip: Kn
    }
}), function (e) {
    "use strict";
    "object" == typeof exports ? module.exports = e(window.jQuery) : "function" == typeof define && define.amd ? define(["jquery"], e) : window.jQuery && !window.jQuery.fn.colorpicker && e(window.jQuery)
}(function (a) {
    "use strict";

    function i(e, t) {
        this.value = {
            h: 0,
            s: 0,
            b: 0,
            a: 1
        }, this.origFormat = null, t && a.extend(this.colors, t), e && (void 0 !== e.toLowerCase ? this.setColor(e += "") : void 0 !== e.h && (this.value = e))
    }

    function r(e, t) {
        var n;
        this.element = a(e).addClass("colorpicker-element"), this.options = a.extend(!0, {}, o, this.element.data(), t), this.component = this.options.component, this.component = !1 !== this.component && this.element.find(this.component), this.component && 0 === this.component.length && (this.component = !1), this.container = !0 === this.options.container ? this.element : this.options.container, this.container = !1 !== this.container && a(this.container), this.input = this.element.is("input") ? this.element : !!this.options.input && this.element.find(this.options.input), this.input && 0 === this.input.length && (this.input = !1), this.color = new i(!1 !== this.options.color ? this.options.color : this.getValue(), this.options.colorSelectors), this.format = !1 !== this.options.format ? this.options.format : this.color.origFormat, !1 !== this.options.color && (this.updateInput(this.color), this.updateData(this.color)), this.picker = a(this.options.template), this.options.customClass && this.picker.addClass(this.options.customClass), this.options.inline ? this.picker.addClass("colorpicker-inline colorpicker-visible") : this.picker.addClass("colorpicker-hidden"), this.options.horizontal && this.picker.addClass("colorpicker-horizontal"), "rgba" !== this.format && "hsla" !== this.format && !1 !== this.options.format || this.picker.addClass("colorpicker-with-alpha"), "right" === this.options.align && this.picker.addClass("colorpicker-right"), !0 === this.options.inline && this.picker.addClass("colorpicker-no-arrow"), this.options.colorSelectors && (a.each((n = this).options.colorSelectors, function (e, t) {
            t = a("<i />").css("background-color", t).data("class", e);
            t.click(function () {
                n.setValue(a(this).css("background-color"))
            }), n.picker.find(".colorpicker-selectors").append(t)
        }), this.picker.find(".colorpicker-selectors").show()), this.picker.on("mousedown.colorpicker touchstart.colorpicker", a.proxy(this.mousedown, this)), this.picker.appendTo(this.container || a("body")), !1 !== this.input && (this.input.on({"keyup.colorpicker": a.proxy(this.keyup, this)}), this.input.on({"change.colorpicker": a.proxy(this.change, this)}), !1 === this.component && this.element.on({"focus.colorpicker": a.proxy(this.show, this)}), !1 === this.options.inline && this.element.on({"focusout.colorpicker": a.proxy(this.hide, this)})), !1 !== this.component && this.component.on({"click.colorpicker": a.proxy(this.show, this)}), !1 === this.input && !1 === this.component && this.element.on({"click.colorpicker": a.proxy(this.show, this)}), !1 !== this.input && !1 !== this.component && "color" === this.input.attr("type") && this.input.on({
            "click.colorpicker": a.proxy(this.show, this),
            "focus.colorpicker": a.proxy(this.show, this)
        }), this.update(), a(a.proxy(function () {
            this.element.trigger("create")
        }, this))
    }

    var o = {
        horizontal: !(i.prototype = {
            constructor: i,
            colors: {
                aliceblue: "#f0f8ff",
                antiquewhite: "#faebd7",
                aqua: "#00ffff",
                aquamarine: "#7fffd4",
                azure: "#f0ffff",
                beige: "#f5f5dc",
                bisque: "#ffe4c4",
                black: "#000000",
                blanchedalmond: "#ffebcd",
                blue: "#0000ff",
                blueviolet: "#8a2be2",
                brown: "#a52a2a",
                burlywood: "#deb887",
                cadetblue: "#5f9ea0",
                chartreuse: "#7fff00",
                chocolate: "#d2691e",
                coral: "#ff7f50",
                cornflowerblue: "#6495ed",
                cornsilk: "#fff8dc",
                crimson: "#dc143c",
                cyan: "#00ffff",
                darkblue: "#00008b",
                darkcyan: "#008b8b",
                darkgoldenrod: "#b8860b",
                darkgray: "#a9a9a9",
                darkgreen: "#006400",
                darkkhaki: "#bdb76b",
                darkmagenta: "#8b008b",
                darkolivegreen: "#556b2f",
                darkorange: "#ff8c00",
                darkorchid: "#9932cc",
                darkred: "#8b0000",
                darksalmon: "#e9967a",
                darkseagreen: "#8fbc8f",
                darkslateblue: "#483d8b",
                darkslategray: "#2f4f4f",
                darkturquoise: "#00ced1",
                darkviolet: "#9400d3",
                deeppink: "#ff1493",
                deepskyblue: "#00bfff",
                dimgray: "#696969",
                dodgerblue: "#1e90ff",
                firebrick: "#b22222",
                floralwhite: "#fffaf0",
                forestgreen: "#228b22",
                fuchsia: "#ff00ff",
                gainsboro: "#dcdcdc",
                ghostwhite: "#f8f8ff",
                gold: "#ffd700",
                goldenrod: "#daa520",
                gray: "#808080",
                green: "#008000",
                greenyellow: "#adff2f",
                honeydew: "#f0fff0",
                hotpink: "#ff69b4",
                indianred: "#cd5c5c",
                indigo: "#4b0082",
                ivory: "#fffff0",
                khaki: "#f0e68c",
                lavender: "#e6e6fa",
                lavenderblush: "#fff0f5",
                lawngreen: "#7cfc00",
                lemonchiffon: "#fffacd",
                lightblue: "#add8e6",
                lightcoral: "#f08080",
                lightcyan: "#e0ffff",
                lightgoldenrodyellow: "#fafad2",
                lightgrey: "#d3d3d3",
                lightgreen: "#90ee90",
                lightpink: "#ffb6c1",
                lightsalmon: "#ffa07a",
                lightseagreen: "#20b2aa",
                lightskyblue: "#87cefa",
                lightslategray: "#778899",
                lightsteelblue: "#b0c4de",
                lightyellow: "#ffffe0",
                lime: "#00ff00",
                limegreen: "#32cd32",
                linen: "#faf0e6",
                magenta: "#ff00ff",
                maroon: "#800000",
                mediumaquamarine: "#66cdaa",
                mediumblue: "#0000cd",
                mediumorchid: "#ba55d3",
                mediumpurple: "#9370d8",
                mediumseagreen: "#3cb371",
                mediumslateblue: "#7b68ee",
                mediumspringgreen: "#00fa9a",
                mediumturquoise: "#48d1cc",
                mediumvioletred: "#c71585",
                midnightblue: "#191970",
                mintcream: "#f5fffa",
                mistyrose: "#ffe4e1",
                moccasin: "#ffe4b5",
                navajowhite: "#ffdead",
                navy: "#000080",
                oldlace: "#fdf5e6",
                olive: "#808000",
                olivedrab: "#6b8e23",
                orange: "#ffa500",
                orangered: "#ff4500",
                orchid: "#da70d6",
                palegoldenrod: "#eee8aa",
                palegreen: "#98fb98",
                paleturquoise: "#afeeee",
                palevioletred: "#d87093",
                papayawhip: "#ffefd5",
                peachpuff: "#ffdab9",
                peru: "#cd853f",
                pink: "#ffc0cb",
                plum: "#dda0dd",
                powderblue: "#b0e0e6",
                purple: "#800080",
                red: "#ff0000",
                rosybrown: "#bc8f8f",
                royalblue: "#4169e1",
                saddlebrown: "#8b4513",
                salmon: "#fa8072",
                sandybrown: "#f4a460",
                seagreen: "#2e8b57",
                seashell: "#fff5ee",
                sienna: "#a0522d",
                silver: "#c0c0c0",
                skyblue: "#87ceeb",
                slateblue: "#6a5acd",
                slategray: "#708090",
                snow: "#fffafa",
                springgreen: "#00ff7f",
                steelblue: "#4682b4",
                tan: "#d2b48c",
                teal: "#008080",
                thistle: "#d8bfd8",
                tomato: "#ff6347",
                turquoise: "#40e0d0",
                violet: "#ee82ee",
                wheat: "#f5deb3",
                white: "#ffffff",
                whitesmoke: "#f5f5f5",
                yellow: "#ffff00",
                yellowgreen: "#9acd32",
                transparent: "transparent"
            },
            _sanitizeNumber: function (e) {
                return "number" == typeof e ? e : isNaN(e) || null === e || "" === e || void 0 === e ? 1 : "" === e ? 0 : void 0 !== e.toLowerCase ? (e.match(/^\./) && (e = "0" + e), Math.ceil(100 * parseFloat(e)) / 100) : 1
            },
            isTransparent: function (e) {
                return !!e && ("transparent" === (e = e.toLowerCase().trim()) || e.match(/#?00000000/) || e.match(/(rgba|hsla)\(0,0,0,0?\.?0\)/))
            },
            rgbaIsTransparent: function (e) {
                return 0 === e.r && 0 === e.g && 0 === e.b && 0 === e.a
            },
            setColor: function (e) {
                (e = e.toLowerCase().trim()) && (this.isTransparent(e) ? this.value = {
                    h: 0,
                    s: 0,
                    b: 0,
                    a: 0
                } : this.value = this.stringToHSB(e) || {h: 0, s: 0, b: 0, a: 1})
            },
            stringToHSB: function (i) {
                i = i.toLowerCase(), void 0 !== this.colors[i] && (i = this.colors[i], o = "alias");
                var o, r = this, s = !1;
                return a.each(this.stringParsers, function (e, t) {
                    var n = t.re.exec(i), n = n && t.parse.apply(r, [n]), t = o || t.format || "rgba";
                    return !n || (s = t.match(/hsla?/) ? r.RGBtoHSB.apply(r, r.HSLtoRGB.apply(r, n)) : r.RGBtoHSB.apply(r, n), r.origFormat = t, !1)
                }), s
            },
            setHue: function (e) {
                this.value.h = 1 - e
            },
            setSaturation: function (e) {
                this.value.s = e
            },
            setBrightness: function (e) {
                this.value.b = 1 - e
            },
            setAlpha: function (e) {
                this.value.a = Math.round(parseInt(100 * (1 - e), 10) / 100 * 100) / 100
            },
            toRGB: function (e, t, n, i) {
                var o, r, s;
                return e || (e = this.value.h, t = this.value.s, n = this.value.b), e = (e *= 360) % 360 / 60, o = r = t = n - (n = n * t), o += [n, s = n * (1 - Math.abs(e % 2 - 1)), 0, 0, s, n][e = ~~e], r += [s, n, n, s, 0, 0][e], t += [0, 0, s, n, n, s][e], {
                    r: Math.round(255 * o),
                    g: Math.round(255 * r),
                    b: Math.round(255 * t),
                    a: i || this.value.a
                }
            },
            toHex: function (e, t, n, i) {
                e = this.toRGB(e, t, n, i);
                return this.rgbaIsTransparent(e) ? "transparent" : "#" + (1 << 24 | parseInt(e.r) << 16 | parseInt(e.g) << 8 | parseInt(e.b)).toString(16).substr(1)
            },
            toHSL: function (e, t, n, i) {
                e = e || this.value.h, t = t || this.value.s, n = n || this.value.b, i = i || this.value.a;
                var o = (2 - t) * n, t = t * n;
                return t /= 0 < o && o <= 1 ? o : 2 - o, o /= 2, 1 < t && (t = 1), {
                    h: isNaN(e) ? 0 : e,
                    s: isNaN(t) ? 0 : t,
                    l: isNaN(o) ? 0 : o,
                    a: isNaN(i) ? 0 : i
                }
            },
            toAlias: function (e, t, n, i) {
                var o, r = this.toHex(e, t, n, i);
                for (o in this.colors) if (this.colors[o] === r) return o;
                return !1
            },
            RGBtoHSB: function (e, t, n, i) {
                var o, r, s;
                return e /= 255, t /= 255, n /= 255, o = 0 == (s = (r = Math.max(e, t, n)) - Math.min(e, t, n)) ? 0 : s / r, {
                    h: this._sanitizeNumber(((0 == s ? null : r === e ? (t - n) / s : r === t ? (n - e) / s + 2 : (e - t) / s + 4) + 360) % 6 * 60 / 360),
                    s: o,
                    b: r,
                    a: this._sanitizeNumber(i)
                }
            },
            HueToRGB: function (e, t, n) {
                return n < 0 ? n += 1 : 1 < n && --n, 6 * n < 1 ? e + (t - e) * n * 6 : 2 * n < 1 ? t : 3 * n < 2 ? e + (t - e) * (2 / 3 - n) * 6 : e
            },
            HSLtoRGB: function (e, t, n, i) {
                t < 0 && (t = 0);
                var t = 2 * n - (n = n <= .5 ? n * (1 + t) : n + t - n * t), o = e, r = e - 1 / 3;
                return [Math.round(255 * this.HueToRGB(t, n, e + 1 / 3)), Math.round(255 * this.HueToRGB(t, n, o)), Math.round(255 * this.HueToRGB(t, n, r)), this._sanitizeNumber(i)]
            },
            toString: function (e) {
                var t = !1;
                switch (e = e || "rgba") {
                    case"rgb":
                        return t = this.toRGB(), this.rgbaIsTransparent(t) ? "transparent" : "rgb(" + t.r + "," + t.g + "," + t.b + ")";
                    case"rgba":
                        return "rgba(" + (t = this.toRGB()).r + "," + t.g + "," + t.b + "," + t.a + ")";
                    case"hsl":
                        return t = this.toHSL(), "hsl(" + Math.round(360 * t.h) + "," + Math.round(100 * t.s) + "%," + Math.round(100 * t.l) + "%)";
                    case"hsla":
                        return t = this.toHSL(), "hsla(" + Math.round(360 * t.h) + "," + Math.round(100 * t.s) + "%," + Math.round(100 * t.l) + "%," + t.a + ")";
                    case"hex":
                        return this.toHex();
                    case"alias":
                        return this.toAlias() || this.toHex();
                    default:
                        return t
                }
            },
            stringParsers: [{
                re: /rgb\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*?\)/,
                format: "rgb",
                parse: function (e) {
                    return [e[1], e[2], e[3], 1]
                }
            }, {
                re: /rgb\(\s*(\d*(?:\.\d+)?)\%\s*,\s*(\d*(?:\.\d+)?)\%\s*,\s*(\d*(?:\.\d+)?)\%\s*?\)/,
                format: "rgb",
                parse: function (e) {
                    return [2.55 * e[1], 2.55 * e[2], 2.55 * e[3], 1]
                }
            }, {
                re: /rgba\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*(?:,\s*(\d*(?:\.\d+)?)\s*)?\)/,
                format: "rgba",
                parse: function (e) {
                    return [e[1], e[2], e[3], e[4]]
                }
            }, {
                re: /rgba\(\s*(\d*(?:\.\d+)?)\%\s*,\s*(\d*(?:\.\d+)?)\%\s*,\s*(\d*(?:\.\d+)?)\%\s*(?:,\s*(\d*(?:\.\d+)?)\s*)?\)/,
                format: "rgba",
                parse: function (e) {
                    return [2.55 * e[1], 2.55 * e[2], 2.55 * e[3], e[4]]
                }
            }, {
                re: /hsl\(\s*(\d*(?:\.\d+)?)\s*,\s*(\d*(?:\.\d+)?)\%\s*,\s*(\d*(?:\.\d+)?)\%\s*?\)/,
                format: "hsl",
                parse: function (e) {
                    return [e[1] / 360, e[2] / 100, e[3] / 100, e[4]]
                }
            }, {
                re: /hsla\(\s*(\d*(?:\.\d+)?)\s*,\s*(\d*(?:\.\d+)?)\%\s*,\s*(\d*(?:\.\d+)?)\%\s*(?:,\s*(\d*(?:\.\d+)?)\s*)?\)/,
                format: "hsla",
                parse: function (e) {
                    return [e[1] / 360, e[2] / 100, e[3] / 100, e[4]]
                }
            }, {
                re: /#?([a-fA-F0-9]{2})([a-fA-F0-9]{2})([a-fA-F0-9]{2})/, format: "hex", parse: function (e) {
                    return [parseInt(e[1], 16), parseInt(e[2], 16), parseInt(e[3], 16), 1]
                }
            }, {
                re: /#?([a-fA-F0-9])([a-fA-F0-9])([a-fA-F0-9])/, format: "hex", parse: function (e) {
                    return [parseInt(e[1] + e[1], 16), parseInt(e[2] + e[2], 16), parseInt(e[3] + e[3], 16), 1]
                }
            }],
            colorNameToHex: function (e) {
                return void 0 !== this.colors[e.toLowerCase()] && this.colors[e.toLowerCase()]
            }
        }),
        inline: !1,
        color: !1,
        format: !1,
        input: "input",
        container: !1,
        component: ".add-on, .input-group-addon",
        sliders: {
            saturation: {maxLeft: 100, maxTop: 100, callLeft: "setSaturation", callTop: "setBrightness"},
            hue: {maxLeft: 0, maxTop: 100, callLeft: !1, callTop: "setHue"},
            alpha: {maxLeft: 0, maxTop: 100, callLeft: !1, callTop: "setAlpha"}
        },
        slidersHorz: {
            saturation: {maxLeft: 100, maxTop: 100, callLeft: "setSaturation", callTop: "setBrightness"},
            hue: {maxLeft: 100, maxTop: 0, callLeft: "setHue", callTop: !1},
            alpha: {maxLeft: 100, maxTop: 0, callLeft: "setAlpha", callTop: !1}
        },
        template: '<div class="colorpicker dropdown-menu"><div class="colorpicker-saturation"><i><b></b></i></div><div class="colorpicker-hue"><i></i></div><div class="colorpicker-alpha"><i></i></div><div class="colorpicker-color"><div /></div><div class="colorpicker-selectors"></div></div>',
        align: "right",
        customClass: null,
        colorSelectors: null
    };
    r.Color = i, r.prototype = {
        constructor: r, destroy: function () {
            this.picker.remove(), this.element.removeData("colorpicker", "color").off(".colorpicker"), !1 !== this.input && this.input.off(".colorpicker"), !1 !== this.component && this.component.off(".colorpicker"), this.element.removeClass("colorpicker-element"), this.element.trigger({type: "destroy"})
        }, reposition: function () {
            if (!1 !== this.options.inline || this.options.container) return !1;
            var e = this.container && this.container[0] !== document.body ? "position" : "offset",
                t = this.component || this.element, e = t[e]();
            "right" === this.options.align && (e.left -= this.picker.outerWidth() - t.outerWidth()), this.picker.css({
                top: e.top + t.outerHeight(),
                left: e.left
            })
        }, show: function (e) {
            if (this.isDisabled()) return !1;
            this.picker.addClass("colorpicker-visible").removeClass("colorpicker-hidden"), this.reposition(), a(window).on("resize.colorpicker", a.proxy(this.reposition, this)), !e || this.hasInput() && "color" !== this.input.attr("type") || e.stopPropagation && e.preventDefault && (e.stopPropagation(), e.preventDefault()), !this.component && this.input || !1 !== this.options.inline || a(window.document).on({"mousedown.colorpicker": a.proxy(this.hide, this)}), this.element.trigger({
                type: "showPicker",
                color: this.color
            })
        }, hide: function () {
            this.picker.addClass("colorpicker-hidden").removeClass("colorpicker-visible"), a(window).off("resize.colorpicker", this.reposition), a(document).off({"mousedown.colorpicker": this.hide}), this.update(), this.element.trigger({
                type: "hidePicker",
                color: this.color
            })
        }, updateData: function (e) {
            return e = e || this.color.toString(this.format), this.element.data("color", e), e
        }, updateInput: function (e) {
            var t;
            return e = e || this.color.toString(this.format), !1 !== this.input && (this.options.colorSelectors && (t = new i(e, this.options.colorSelectors).toAlias(), void 0 !== this.options.colorSelectors[t] && (e = t)), this.input.prop("value", e)), e
        }, updatePicker: function (e) {
            void 0 !== e && (this.color = new i(e, this.options.colorSelectors));
            var t = !1 === this.options.horizontal ? this.options.sliders : this.options.slidersHorz,
                n = this.picker.find("i");
            if (0 !== n.length) return !1 === this.options.horizontal ? (t = this.options.sliders, n.eq(1).css("top", t.hue.maxTop * (1 - this.color.value.h)).end().eq(2).css("top", t.alpha.maxTop * (1 - this.color.value.a))) : (t = this.options.slidersHorz, n.eq(1).css("left", t.hue.maxLeft * (1 - this.color.value.h)).end().eq(2).css("left", t.alpha.maxLeft * (1 - this.color.value.a))), n.eq(0).css({
                top: t.saturation.maxTop - this.color.value.b * t.saturation.maxTop,
                left: this.color.value.s * t.saturation.maxLeft
            }), this.picker.find(".colorpicker-saturation").css("backgroundColor", this.color.toHex(this.color.value.h, 1, 1, 1)), this.picker.find(".colorpicker-alpha").css("backgroundColor", this.color.toHex()), this.picker.find(".colorpicker-color, .colorpicker-color div").css("backgroundColor", this.color.toString(this.format)), e
        }, updateComponent: function (e) {
            var t;
            return e = e || this.color.toString(this.format), !1 !== this.component && (0 < (t = this.component.find("i").eq(0)).length ? t : this.component).css({backgroundColor: e}), e
        }, update: function (e) {
            var t;
            return !1 === this.getValue(!1) && !0 !== e || (t = this.updateComponent(), this.updateInput(t), this.updateData(t), this.updatePicker()), t
        }, setValue: function (e) {
            this.color = new i(e, this.options.colorSelectors), this.update(!0), this.element.trigger({
                type: "changeColor",
                color: this.color,
                value: e
            })
        }, getValue: function (e) {
            var t;
            return e = void 0 === e ? "#000000" : e, t = void 0 !== (t = this.hasInput() ? this.input.val() : this.element.data("color")) && "" !== t && null !== t ? t : e
        }, hasInput: function () {
            return !1 !== this.input
        }, isDisabled: function () {
            return !!this.hasInput() && !0 === this.input.prop("disabled")
        }, disable: function () {
            return !!this.hasInput() && (this.input.prop("disabled", !0), this.element.trigger({
                type: "disable",
                color: this.color,
                value: this.getValue()
            }), !0)
        }, enable: function () {
            return !!this.hasInput() && (this.input.prop("disabled", !1), this.element.trigger({
                type: "enable",
                color: this.color,
                value: this.getValue()
            }), !0)
        }, currentSlider: null, mousePointer: {left: 0, top: 0}, mousedown: function (e) {
            !e.pageX && !e.pageY && e.originalEvent && e.originalEvent.touches && (e.pageX = e.originalEvent.touches[0].pageX, e.pageY = e.originalEvent.touches[0].pageY), e.stopPropagation(), e.preventDefault();
            var t = a(e.target).closest("div"),
                n = this.options.horizontal ? this.options.slidersHorz : this.options.sliders;
            if (!t.is(".colorpicker")) {
                if (t.is(".colorpicker-saturation")) this.currentSlider = a.extend({}, n.saturation); else if (t.is(".colorpicker-hue")) this.currentSlider = a.extend({}, n.hue); else {
                    if (!t.is(".colorpicker-alpha")) return !1;
                    this.currentSlider = a.extend({}, n.alpha)
                }
                n = t.offset();
                this.currentSlider.guide = t.find("i")[0].style, this.currentSlider.left = e.pageX - n.left, this.currentSlider.top = e.pageY - n.top, this.mousePointer = {
                    left: e.pageX,
                    top: e.pageY
                }, a(document).on({
                    "mousemove.colorpicker": a.proxy(this.mousemove, this),
                    "touchmove.colorpicker": a.proxy(this.mousemove, this),
                    "mouseup.colorpicker": a.proxy(this.mouseup, this),
                    "touchend.colorpicker": a.proxy(this.mouseup, this)
                }).trigger("mousemove")
            }
            return !1
        }, mousemove: function (e) {
            !e.pageX && !e.pageY && e.originalEvent && e.originalEvent.touches && (e.pageX = e.originalEvent.touches[0].pageX, e.pageY = e.originalEvent.touches[0].pageY), e.stopPropagation(), e.preventDefault();
            var t = Math.max(0, Math.min(this.currentSlider.maxLeft, this.currentSlider.left + ((e.pageX || this.mousePointer.left) - this.mousePointer.left))),
                e = Math.max(0, Math.min(this.currentSlider.maxTop, this.currentSlider.top + ((e.pageY || this.mousePointer.top) - this.mousePointer.top)));
            return this.currentSlider.guide.left = t + "px", this.currentSlider.guide.top = e + "px", this.currentSlider.callLeft && this.color[this.currentSlider.callLeft].call(this.color, t / this.currentSlider.maxLeft), this.currentSlider.callTop && this.color[this.currentSlider.callTop].call(this.color, e / this.currentSlider.maxTop), "setAlpha" === this.currentSlider.callTop && !1 === this.options.format && (1 !== this.color.value.a ? (this.format = "rgba", this.color.origFormat = "rgba") : (this.format = "hex", this.color.origFormat = "hex")), this.update(!0), this.element.trigger({
                type: "changeColor",
                color: this.color
            }), !1
        }, mouseup: function (e) {
            return e.stopPropagation(), e.preventDefault(), a(document).off({
                "mousemove.colorpicker": this.mousemove,
                "touchmove.colorpicker": this.mousemove,
                "mouseup.colorpicker": this.mouseup,
                "touchend.colorpicker": this.mouseup
            }), !1
        }, change: function (e) {
            this.keyup(e)
        }, keyup: function (e) {
            38 === e.keyCode ? (this.color.value.a < 1 && (this.color.value.a = Math.round(100 * (this.color.value.a + .01)) / 100), this.update(!0)) : 40 === e.keyCode ? (0 < this.color.value.a && (this.color.value.a = Math.round(100 * (this.color.value.a - .01)) / 100), this.update(!0)) : (this.color = new i(this.input.val(), this.options.colorSelectors), this.color.origFormat && !1 === this.options.format && (this.format = this.color.origFormat), !1 !== this.getValue(!1) && (this.updateData(), this.updateComponent(), this.updatePicker())), this.element.trigger({
                type: "changeColor",
                color: this.color,
                value: this.input.val()
            })
        }
    }, a.colorpicker = r, a.fn.colorpicker = function (n) {
        var i = Array.prototype.slice.call(arguments, 1), e = 1 === this.length, o = null, t = this.each(function () {
            var e = a(this), t = e.data("colorpicker");
            t || (t = new r(this, "object" == typeof n ? n : {}), e.data("colorpicker", t)), o = "string" == typeof n ? a.isFunction(t[n]) ? t[n].apply(t, i) : (i.length && (t[n] = i[0]), t[n]) : e
        });
        return e ? o : t
    }, a.fn.colorpicker.constructor = r
});
