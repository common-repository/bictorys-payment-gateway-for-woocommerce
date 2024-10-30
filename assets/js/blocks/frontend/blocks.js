! function() {
    "use strict";
    var e = window.wp.element,
        t = window.wp.i18n,
        a = window.wc.wcBlocksRegistry,
        n = window.wp.htmlEntities;
    const l = (0, window.wc.wcSettings.getSetting)("bictorys_data", {}),
        s = (0, t.__)("Bictorys ", "bictorys-payment-gateway-for-woocommerce"),
        i = (0, n.decodeEntities)(l.title) || s,
        c = () => (0, n.decodeEntities)(l.description || ""),
        o = {
            name: "bictorys",
            className: "bictorys-custom-container",
            label: (0, e.createElement)((() => (0, e.createElement)(e.Fragment, null, (0, e.createElement)("div", {
                className: "bictorys-custom-label",
            }, i, (0, e.createElement)("img", {
                className: "bictorys-custom-logo",
                src: l.logo_url,
                alt: i
            })))), null),
            content: (0, e.createElement)((() => (0, e.createElement)(e.Fragment, null, (0, e.createElement)("div", {
                className: "bictorys-custom-description",
            }, (l.description || ""), ))), null),
            edit: (0, e.createElement)(c, null),
            canMakePayment: () => !0,
            ariaLabel: i,
            supports: {
                showSavedCards: l.allow_saved_cards,
                showSaveOption: l.allow_saved_cards,
                features: l.supports
            }
        };
    (0, a.registerPaymentMethod)(o)
}();