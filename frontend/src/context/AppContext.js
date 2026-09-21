import React, { createContext, useContext, useMemo, useState } from "react";
import { translations } from "../i18n/translations";

const AppContext = createContext(null);

export function AppProvider({ children }) {
  const [lang, setLang] = useState("fr"); // 'fr' | 'ar'

  const value = useMemo(() => {
    const isRTL = lang === "ar";
    const dict = translations[lang];
    const t = (key) => dict[key] ?? key;
    return { lang, setLang, isRTL, t };
  }, [lang]);

  return <AppContext.Provider value={value}>{children}</AppContext.Provider>;
}

export function useApp() {
  const ctx = useContext(AppContext);
  if (!ctx) throw new Error("useApp must be used within AppProvider");
  return ctx;
}
