import {
  V as N,
  r as f,
  P as y,
  F as B,
  I as C,
  W as I,
  m as S,
  $ as b,
  ao as L,
  p as e,
  Z as i,
  ap as p,
  ad as U,
  D as V,
  aq as k,
  ar as F,
  o as D,
  N as H,
} from "./common.modules-3e3c160a.js";
import { d as P, aC as T } from "./page-activity-ActivityDetail-f8bc6e8e.js";
window.getBuildInfo = function () {
  return {
    buildTime: "4/19/2025, 2:01:23 PM",
    branch: " commitId:8f9a6b77f997bcd1b23c3d9fa0b70bf5488d0b97",
  };
};
const g = (r) => (k("data-v-88ca4a94"), (r = r()), F(), r),
  R = { class: "maintenace" },
  j = ["src"],
  E = { class: "maintenace-body" },
  G = g(() =>
    e(
      "div",
      { class: "text1" },
      [
        p("Under Maintenance "),
        e("div", { class: "text2" }, "Server is expected to be launched in"),
      ],
      -1
    )
  ),
  q = { class: "count-down" },
  A = { key: 0, class: "notes" },
  O = g(() => e("div", { class: "notes-head" }, "Update Notes", -1)),
  W = { class: "notes-body" },
  Z = ["innerHTML"],
  $ = "/api/webapi",
  K = N({
    __name: "index",
    setup(r) {
      var h;
      const v =
          ((h = window.CONFIG) == null ? void 0 : h.VITE_API_URL) ||
          "https://joshgame.online/application",
        { projectIcon: w } = P(),
        s = f(0),
        c = y(() => {
          const t = Math.floor(s.value / 3600),
            o = Math.floor((s.value % 3600) / 60),
            n = Math.floor(s.value % 60),
            a = String(t).padStart(2, "0"),
            u = String(o).padStart(2, "0"),
            m = String(n).padStart(2, "0");
          return {
            h1: parseInt(a[0], 10),
            h2: parseInt(a[1], 10),
            m1: parseInt(u[0], 10),
            m2: parseInt(u[1], 10),
            s1: parseInt(m[0], 10),
            s2: parseInt(m[1], 10),
          };
        }),
        d = f("");
      let l = null;
      const M = async () => {
          V.get(v + $ + "/GetMaintenanceInfo")
            .then((t) => {
              var o, n, a;
              if (t.data.enabled === !1) return T.push({ name: "login" });
              (d.value = ((o = t.data) == null ? void 0 : o.notifyTip) || ""),
                (s.value = x(
                  (n = t.data) == null ? void 0 : n.serverTime,
                  (a = t.data) == null ? void 0 : a.endTime
                )),
                s.value > 0 ? _() : T.push({ name: "login" });
            })
            .catch((t) => {});
        },
        _ = () => {
          clearInterval(l),
            (l = setTimeout(() => {
              s.value--, s.value > 0 && _();
            }, 1e3));
        },
        x = (t, o) => {
          const n = new Date(t.replace(" ", "T"));
          return (new Date(o.replace(" ", "T")) - n) / 1e3;
        };
      return (
        B(() => {
          M();
        }),
        C(() => {
          clearInterval(l);
        }),
        (t, o) => {
          const n = I("NavBar"),
            a = I("svg-icon");
          return (
            D(),
            S("div", R, [
              b(n, null, {
                center: L(() => [
                  e(
                    "img",
                    { src: H(w), alt: "", style: { height: "100%" } },
                    null,
                    8,
                    j
                  ),
                ]),
                _: 1,
              }),
              e("div", E, [
                b(a, { name: "maintenace", class: "main-svg" }),
                G,
                e("div", q, [
                  e("div", null, i(c.value.h1), 1),
                  e("div", null, i(c.value.h2), 1),
                  p(": "),
                  e("div", null, i(c.value.m1), 1),
                  e("div", null, i(c.value.m2), 1),
                  p(": "),
                  e("div", null, i(c.value.s1), 1),
                  e("div", null, i(c.value.s2), 1),
                ]),
                d.value
                  ? (D(),
                    S("div", A, [
                      O,
                      e("div", W, [
                        e("div", { innerHTML: d.value }, null, 8, Z),
                      ]),
                    ]))
                  : U("v-if", !0),
              ]),
            ])
          );
        }
      );
    },
  });
export { K as _ };
//# sourceMappingURL=page-maintenance-index.vue_vue_type_script_setup_true_lang.ts-61f1f330.js.map
