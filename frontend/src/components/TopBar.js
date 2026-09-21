import React from "react";
import { View, Text, TouchableOpacity, StyleSheet } from "react-native";
import { useApp } from "../context/AppContext";
import { colors, radii, spacing } from "../theme/theme";

export default function TopBar({ view, onChangeView }) {
  const { lang, setLang, t, isRTL } = useApp();

  const views = [
    { id: "home", label: t("home") },
    { id: "emergency", label: t("emergency") },
    { id: "tracking", label: t("tracking") },
  ];

  return (
    <View style={[styles.wrap, isRTL && { flexDirection: "row-reverse" }]}>
      <View style={[styles.segment, isRTL && { flexDirection: "row-reverse" }]}>
        {views.map((v) => (
          <TouchableOpacity
            key={v.id}
            onPress={() => onChangeView(v.id)}
            style={[styles.segmentItem, view === v.id && styles.segmentItemActive]}
          >
            <Text
              style={[
                styles.segmentText,
                view === v.id && styles.segmentTextActive,
              ]}
              numberOfLines={1}
            >
              {v.label}
            </Text>
          </TouchableOpacity>
        ))}
      </View>

      <View style={[styles.langToggle, isRTL && { flexDirection: "row-reverse" }]}>
        <TouchableOpacity
          onPress={() => setLang("fr")}
          style={[styles.langBtn, lang === "fr" && styles.langBtnActive]}
        >
          <Text style={[styles.langText, lang === "fr" && styles.langTextActive]}>
            FR
          </Text>
        </TouchableOpacity>
        <TouchableOpacity
          onPress={() => setLang("ar")}
          style={[styles.langBtn, lang === "ar" && styles.langBtnActive]}
        >
          <Text style={[styles.langText, lang === "ar" && styles.langTextActive]}>
            ع
          </Text>
        </TouchableOpacity>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  wrap: {
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-between",
    paddingHorizontal: spacing.md,
    paddingTop: spacing.sm,
    paddingBottom: spacing.sm,
    backgroundColor: colors.bgDeep,
    borderBottomWidth: 1,
    borderBottomColor: colors.borderMuted,
  },
  segment: {
    flexDirection: "row",
    backgroundColor: colors.bgCardAlt,
    borderRadius: radii.pill,
    padding: 3,
    flex: 1,
    marginEnd: spacing.sm,
  },
  segmentItem: {
    flex: 1,
    paddingVertical: 6,
    borderRadius: radii.pill,
    alignItems: "center",
  },
  segmentItemActive: { backgroundColor: colors.amber },
  segmentText: { fontSize: 11, fontWeight: "700", color: colors.textMuted },
  segmentTextActive: { color: "#1C1300" },
  langToggle: {
    flexDirection: "row",
    backgroundColor: colors.bgCardAlt,
    borderRadius: radii.pill,
    padding: 3,
  },
  langBtn: { paddingVertical: 6, paddingHorizontal: 10, borderRadius: radii.pill },
  langBtnActive: { backgroundColor: colors.orange },
  langText: { fontSize: 11, fontWeight: "700", color: colors.textMuted },
  langTextActive: { color: "#fff" },
});
