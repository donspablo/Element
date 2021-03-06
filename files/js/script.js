const _window = window;
!function () {
    function e(e, i) {
        let r = document.createElement("script");
        r.onload = r.onreadystatechange = function () {
            (r.onload = r.onreadystatechange = null, i && i(r), r = null)
        }, r.async = !0, r.src = e, n.insertBefore(r, t)
    }

    var t = document.getElementsByTagName("script")[0], n = t.parentNode;
    _window.sssl = function (t, n) {
        "string" === typeof t ? e(t, n) : e(t.shift(), function () {
            t.length ? _window.sssl(t, n) : n && n()
        })
    }
}(), _window.addEventListener("load", () => {
    setTimeout(loaded, 1500)
});
!function (e, t) {
    "use strict";

    function n(t) {
        t.toElement = undefined;
        t.toElement = undefined;
        let n = t.target || t.toElement;
        if ("notifi-wrapper" !== n.getAttribute("id")) {
            for (var o = !1; !r(n, "notifi-notification");) r(n, "notifi-close") && (o = !0), r(n, "btn-close-notification") && (o = !0), n = n.parentElement;
            let i = n.getAttribute("id");
            i = /notifi-notification-([a-zA-Z0-9]+)/.exec(i)[1], o && s.notifications[i].options.dismissable ? s.removeNotification(i) : null !== (t = s.notifications[i].action) && ("string" === typeof t ? e.location = t : "function" === typeof t ? t(i) : (console.log("Notifi Error: Invalid click action:"), console.log(t)))
        }
    }

    function o(e) {
        let n;
        void 0 === s.notifications[e] && (s.notifications[e] = {}), null !== s.notifications[e].element && void 0 !== s.notifications[e].element || (i(n = d.cloneNode(!0), "notifi-notification"), n.setAttribute("id", "notifi-notification-" + e), s.notifications[e].element = n), null === s.notifications[e].element.parentElement && (n = t.getElementById("notifi-wrapper"), 480 < t.body.clientWidth ? n.appendChild(s.notifications[e].element) : n.insertBefore(s.notifications[e].element, n.firstChild))
    }

    function i(e, t) {
        r(e, t) || (e.className += " " + t)
    }

    function r(e, t) {
        return t = new RegExp("(?:^|\\s)" + t + "(?!\\S)", "g"), null !== e.className.match(t)
    }

    function a(e, t) {
        t = new RegExp("(?:^|\\s)" + t + "(?!\\S)", "g"), e.className = e.className.replace(t, "")
    }

    function c() {
        const e = t.createElement("div");
        e.setAttribute("id", "notifi-wrapper"), e.addEventListener("click", n), t.body.appendChild(e), s.setNotificationHTML('<div class="notifi-notification"><div class="notifi-icon"></div><h3 class="notifi-title"></h3><p class="notifi-text"></p><p class="notifi-button"></p><div class="notifi-close"><i class="fa fa-times"></i></div></div>')
    }

    t.attachEvent = undefined;
    t.attachEvent = undefined;
    var s = s || {}, d = (s = {
        count: 0,
        notifications: {},
        defaultOptions: {
            color: "default",
            title: "",
            text: "",
            icon: "",
            timeout: 5e3,
            action: null,
            button: null,
            dismissable: !0
        },
        setNotificationHTML: function (e) {
            const n = t.createElement("div");
            n.innerHTML = e, e = n.firstChild, n.removeChild(e), d = e
        },
        addNotification: function (e) {
            s.count += 1;
            const t = function () {
                let e = "";
                const t = "abcdefghijklmnopqrstuvwxyz0123456789";
                do {
                    e = "";
                    for (let n = 0; n < 5; n++) e += t.charAt(Math.floor(Math.random() * t.length))
                } while (s.exists(e));
                return e
            }();
            return o(t), s.editNotification(t, e), t
        },
        editNotification: function (t, n) {
            null !== s.notifications[t].removeTimer && (clearTimeout(s.notifications[t].removeTimer), s.notifications[t].removeTimer = null), o(t), n = n || {}, void 0 === s.notifications[t].options && (s.notifications[t].options = s.defaultOptions), n = function (e, t) {
                let n;
                const o = {};
                for (n in e) o[n] = e[n];
                for (n in t) o[n] = t[n];
                return o
            }(s.notifications[t].options, n);
            const r = s.notifications[t].element;
            i(r, n.color);
            let c = r.getElementsByClassName("notifi-title")[0];
            n.title ? (c.textContent = n.title, a(r, "notifi-no-title")) : (c.textContent = "", i(r, "notifi-no-title")), c = r.getElementsByClassName("notifi-text")[0], n.text ? (c.textContent = n.text, a(r, "notifi-no-text")) : (c.textContent = "", i(r, "notifi-no-text")), c = r.getElementsByClassName("notifi-icon")[0], n.icon ? (c.innerHTML = n.icon, a(r, "notifi-no-icon")) : (c.innerHTML = "", i(r, "notifi-no-icon")), null !== n.timer && clearTimeout(s.notifications[t].timer), (c = null) !== n.timeout && (c = setTimeout(function () {
                s.removeNotification(t)
            }, n.timeout)), s.notifications[t].timer = c, s.notifications[t].action = n.action, c = r.getElementsByClassName("notifi-button")[0], n.button ? c.innerHTML = n.button : c.innerHTML = "", (n.dismissable ? a : i)(r, "not-dismissable"), setTimeout(function () {
                i(r, "notifi-in"), r.removeAttribute("style")
            }, 0), ("ontouchstart" in e || "onmsgesturechange" in e) && i(r, "no-hover"), s.notifications[t].options = n
        },
        removeNotification: function (e) {
            if (s.isDismissed(e)) return !1;
            const n = s.notifications[e].element;
            return a(n, "notifi-in"), 480 < t.body.clientWidth ? n.style.marginBottom = -n.offsetHeight + "px" : n.style.marginTop = -n.offsetHeight + "px", s.notifications[e].removeTimer = setTimeout(function () {
                n.parentElement.removeChild(n)
            }, 500), clearTimeout(s.notifications[e].timer), !0
        },
        isDismissed: function (e) {
            return !s.exists(e) || null === s.notifications[e].element.parentElement
        },
        exists: function (e) {
            return void 0 !== s.notifications[e]
        }
    }, null);
    "complete" === t.readyState || "interactive" === t.readyState && t.body ? c() : t.addEventListener ? t.addEventListener("DOMContentLoaded", function () {
        t.removeEventListener("DOMContentLoaded", null, !1), c()
    }, !1) : t.attachEvent && t.attachEvent("onreadystatechange", function () {
        "complete" === t.readyState && (t.detachEvent("onreadystatechange", null), c())
    }), e.Notifi = s
}(window, document), sssl(["/js/signIn.js", "/js/signOut.js"], function () {
        document.querySelector("[required='required']") && document.querySelector("[required='required']").blur(function () {
            document.querySelector(this).val() ? document.querySelector(this).hasClass("hasError") && document.querySelector(this).removeClass("hasError") : document.querySelector(this).classList.add("hasError")
        });
        document.querySelector("[required='required']") && document.querySelector("[required='required']").change(function () {
            document.querySelector(this).hasClass("hasError") && document.querySelector(this).removeClass("hasError")
        });
        document.querySelector(".autosize") && document.querySelector(".autosize").each(function () {
            resizeTextArea(document.querySelector(this))
        });
        let e = null;
        document.querySelector("#signout") && document.querySelector("#signout").click(function (t) {


            let cancelOption;
            let yesOption;
            let accountSignOutText;
            let accountSignOutTitle;
            t.preventDefault(), Notifi.isDismissed(e) && (e = Notifi.addNotification({
                color: "default",
                title: accountSignOutTitle,
                text: accountSignOutText,
                icon: '<i class="fa fa-sign-out fa-lg"></i>',
                button: '<a href="index.php?action=signout" class="btn btn-success btn-notifi">' + yesOption + '</a> <span id="cancel-signout" class="btn btn-warning btn-notifi btn-close-notification">' + cancelOption + "</span>",
                timeout: null
            }))
        }), function () {
            function e() {
                const e = t[Math.floor(Math.random() * (t.length - 1)) + 1];
                dashboard.$app.trigger("dashboard.messageReceived", e)
            }

            document.querySelector("#scheduler").kendoScheduler({
                date: new Date,
                startTime: new Date,
                height: 600,
                views: ["day", {type: "workWeek", selected: !0}, "week", "month", "agenda", {
                    type: "timeline",
                    eventHeight: 50
                }],
                timezone: "Etc/UTC",
                dataSource: {
                    batch: !0,
                    transport: {
                        read: {url: "", dataType: "jsonp"},
                        update: {url: "", dataType: "jsonp"},
                        create: {url: "", dataType: "jsonp"},
                        destroy: {url: "", dataType: "jsonp"}
                    },
                    schema: {
                        model: {
                            id: "taskId",
                            fields: {
                                taskId: {from: "TaskID", type: "number"},
                                title: {from: "Title", defaultValue: "No title", validation: {required: !0}},
                                start: {type: "date", from: "Start"},
                                end: {type: "date", from: "End"},
                                startTimezone: {from: "StartTimezone"},
                                endTimezone: {from: "EndTimezone"},
                                description: {from: "Description"},
                                recurrenceId: {from: "RecurrenceID"},
                                recurrenceRule: {from: "RecurrenceRule"},
                                recurrenceException: {from: "RecurrenceException"},
                                ownerId: {from: "OwnerID", defaultValue: 1},
                                isAllDay: {type: "boolean", from: "IsAllDay"}
                            }
                        }
                    }
                },
                resources: [{
                    field: "ownerId",
                    title: "Owner",
                    dataSource: [{text: "Alex", value: 1, color: "#f8a398"}, {
                        text: "Bob",
                        value: 2,
                        color: "#51a0ed"
                    }, {text: "Charlie", value: 3, color: "#56ca85"}]
                }]
            }), new resetStar;
            var t = [{type: "regionEntrance", userId: "Bob Saget", regionId: "cid10_chi_lobby"}, {
                type: "regionEntrance",
                userId: "Bob Saget",
                regionId: "cid10_chi_lobby"
            }, {type: "regionExit", userId: "Bob Saget", regionId: "cid10_chi_lobby"}, {
                type: "regionEntrance",
                userId: "Jeff",
                regionId: "cid10_chi_conf_2"
            }, {type: "regionExit", userId: "Jeff", regionId: "cid10_chi_conf_2"}, {
                type: "regionEntrance",
                userId: "Paul",
                regionId: "cid10_chi_lobby"
            }, {type: "regionExit", userId: "Paul", regionId: "cid10_chi_lobby"}, {
                type: "regionEntrance",
                userId: "Paul",
                regionId: "cid10_chi_conf_2"
            }, {type: "regionEntrance", userId: "DenverCoder9", regionId: "cid10_chi_lobby"}, {
                type: "regionEntrance",
                userId: "Trogdor",
                regionId: "cid10_chi_lobby"
            }, {type: "coffeeBrewed", when: "9:37am"}, {type: "coffeeDepleted", when: "10:15am"}, {
                type: "coffeeBrewed",
                when: "10:30am"
            }];
            _window.dashboard = {
                $app: document.querySelector("#app"),
                dispatcher: setInterval(e, 5e3)
            }, dashboard.$app.on("dashboard.messageReceived", function (e, t) {
                console.log(t)
            }), document.querySelector("#welcome").click(e), dashboard.$app.find(".welcome-component").each(function (e, t) {
                function n(e) {
                    return document.querySelector('h2').text(e)
                }

                function o(e) {
                    return l.some(function (t) {
                        return e === t
                    })
                }

                function i() {
                    d.shift().remove()
                }

                var r = document.querySelector(t), a = r.data("region"), c = r.data("message-welcome"),
                    s = r.data("message-farewell"), d = [], l = [];
                dashboard.$app.on("dashboard.messageReceived", function (e, t) {
                    if (t.regionId !== a) return !0;
                    if ("regionEntrance" !== t.type && "regionExit" !== t.type) return !0;
                    let u, f, m = !1;
                    const h = t.userId, p = t.type;
                    t = ("regionEntrance" === p ? c : s).replace("{$user}", h), o(h) || "regionEntrance" !== p ? o(h) && "regionExit" === p && (u = n(t), l.splice(l.indexOf(h), 1), f = h, r.find("h2").each(function (e, t) {
                        (t = document.querySelector(t)).data("user") === f && (console.log("activeMessages before", d.length), d.splice(d.indexOf(t), 1), t.remove(), console.log("activeMessages after", d.length))
                    }), m = !0) : (u = n(t).data("user", h), l.push(h), m = !0), m && (r.append(u), d.push(u), setTimeout(i, 7275))
                })
            }), dashboard.$app.find(".connected-users").each(function (e, t) {
                function n() {
                    const e = 1 === a.length ? r.replace("users", "user") : r;
                    o.find("h3").text(e.replace("{$num}", a.length))
                }

                var o = document.querySelector(t), i = o.data("region"), r = o.data("message"), a = [];
                dashboard.$app.on("dashboard.messageReceived", function (e, t) {
                    return t.regionId !== i || ("regionEntrance" === t.type && -1 === a.indexOf(t.userId) && (a.push(t.userId), n()), "regionExit" === t.type && -1 !== (o = a.indexOf(t.userId)) && (a.splice(o, 1), n())), !0;
                    var o
                })
            }), dashboard.$app.find(".coffee-brewed").each(function (e, t) {
                const n = document.querySelector(t), o = n.data("state"), i = n.data("message-brewed"),
                    r = n.data("message-depleted");
                dashboard.$app.on("dashboard.messageReceived", function (e, t) {
                    if ("coffeeBrewed" !== t.type && "coffeeDepleted" !== t.type) return !0;
                    if ("coffeeDepleted" === t.type && o === t.type) return !0;
                    const a = ("coffeeBrewed" === t.type ? i : r).replace("{$time}", t.when);
                    return n.data("state", t.type), n.html(a), !0
                })
            }), dashboard.$app.find(".conference-rooms").each(function (e, t) {
                    const n = (t = document.querySelector(t)).data("message"), o = t.data("empty-verbiage"),
                        i = t.data("full-verbiage"), r = {};

                    function a(e) {
                        const t = r[e];
                        e = n.replace("{$room}", t.name), e = 0 < t.users.length ? e.replace("{$status}", i) : e.replace("{$status}", o), t.$el.html(e)
                    }

                    t.find("h3").each(function (e, t) {
                        r[t.id] = {
                            id: t.id,
                            $el: document.querySelector(t),
                            name: document.querySelector(t).data("room-name"),
                            users: []
                        }
                    }), dashboard.$app.on("dashboard.messageReceived", function (e, t) {
                        if ("regionEntrance" !== t.type && "regionExit" !== t.type || !r.hasOwnProperty(t.regionId)) return !0;
                        const n = r[t.regionId], o = n.users.some(function (e) {
                            return t.userId === e
                        });
                        "regionEntrance" === t.type ? o || (n.users.push(t.userId), a(n.id)) : "regionExit" === t.type && o && (n.users.splice(n.users.indexOf(t.userId), 1), a(n.id))
                    })
                }
            )
        }
    }
)


let THIS = null;

function getEl() {
    return THIS || (el = document.querySelector(".starwars"), audio = el.find("audio").get(0), start = el.find(".start"), animation = el.find(".animation"), THIS = this)
}

function resetStar() {

    let _start;
    _this = getEl(), _start.show(), _cloned = _animation.clone(!0), _animation.remove(), _animation = _cloned, THIS = _this
}

function Login() {


    let _el;
    let _audio;
    let _start;
    document.querySelector("html").classList.add("login"), document.querySelector(".starwars").classList.add("on"), _this = getEl(), _start.hide(), _audio.play(), _el.append(_animation), THIS = _this
}

function onScroll() {
    checkHeaderPosition(), findCurrentTabSelector(), lastScroll = window.scrollTop()
}

function onResize() {
    currentId && setSliderCss()
}

function checkHeaderPosition() {
    75 < window.scrollTop() ? document.querySelector(".header").classList.add("header--scrolled") : document.querySelector(".header").removeClass("header--scrolled");
    const e = document.querySelector(".nav").offset().top + document.querySelector(".nav").height() - tabContainerHeight - 75;
    window.scrollTop() > lastScroll && window.scrollTop() > e ? (document.querySelector(".header").classList.add("et-header--move-up"), document.querySelector(".nav-container").removeClass("nav-container--top-first"), document.querySelector(".nav-container").classList.add("nav-container--top-second")) : window.scrollTop() < lastScroll && window.scrollTop() > e ? (document.querySelector(".header").removeClass("et-header--move-up"), document.querySelector(".et-hero-tabs-container").classList.add("et-hero-tabs-container--top-first")) : (document.querySelector(".header").removeClass("header--move-up"), document.querySelector(".nav-container").removeClass("nav-container--top-first"), document.querySelector(".nav-container").removeClass("nav-container--top-second"))
}

function findCurrentTabSelector() {
    let t, o, r = this;
    r.tabContainerHeight = undefined;
    r.tabContainerHeight = undefined;
    r.tabContainerHeight = undefined;
    r.tabContainerHeight = undefined;
    document.querySelector(".nav-tab").each(function () {
        const e = document.querySelector(this).attr("href"),
            n = document.querySelector(e).offset().top - r.tabContainerHeight,
            c = document.querySelector(e).offset().top + document.querySelector(e).height() - r.tabContainerHeight;
        window.scrollTop() > n && window.scrollTop() < c && (t = e, o = document.querySelector(this))
    }), currentId === t && null !== currentId || (currentId = t, currentTab = o, setSliderCss())
}

function setSliderCss() {
    let e = 0, t = 0;
    currentTab && (e = currentTab.css("width"), t = currentTab.offset().left), document.querySelector(".nav-tab-slider").css("width", e), document.querySelector(".nav-tab-slider").css("left", t)
}

function loaded() {
    if (document.querySelector("html").classList.add("loaded"), "serviceWorker" in navigator) try {
        navigator.serviceWorker.register("/sw.js")
    } catch (e) {
        console.log("SW registration failed")
    }
    currentId = null, currentTab = null, tabContainerHeight = 70, lastScroll = 0;
    const e = this;
    document.querySelector(".nav-tab").click(function () {
        e.onTabClick(event, document.querySelector(this))
    }), window.scroll(() => {
        onScroll()
    })
}

window.addEventListener('resize', function () {
    onResize()
}, true);

setTimeout(loaded, 3e4);