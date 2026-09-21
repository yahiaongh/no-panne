import React from "react";
import { View, Text, StyleSheet } from "react-native";
import { colors, radii, spacing, type, shadow } from "../theme/theme";

export function Card({ children, style }) {
  return <View style={[styles.card, style]}>{children}</View>;
}

export function Badge({ label, tone = "neutral", style }) {
  const palette = {
    neutral: { bg: colors.bgCardAlt, fg: colors.textSecondary, bd: colors.border },
    success: { bg: colors.successBg, fg: colors.success, bd: "#1C4A2C" },
    amber: { bg: "rgba(245,158,11,0.12)", fg: colors.amber, bd: "rgba(245,158,11,0.35)" },
    muted: { bg: colors.bgCardAlt, fg: colors.textMuted, bd: colors.borderMuted },
  }[tone];

  return (
    <View
      style={[
        styles.badge,
        { backgroundColor: palette.bg, borderColor: palette.bd },
        style,
      ]}
    >
      <Text style={[styles.badgeText, { color: palette.fg }]}>{label}</Text>
    </View>
  );
}

export function SectionTitle({ children, style }) {
  return <Text style={[styles.sectionTitle, style]}>{children}</Text>;
}

const styles = StyleSheet.create({
  card: {
    backgroundColor: colors.bgCard,
    borderRadius: radii.lg,
    borderWidth: 1,
    borderColor: colors.border,
    padding: spacing.lg,
    ...shadow.card,
  },
  badge: {
    borderWidth: 1,
    borderRadius: radii.pill,
    paddingVertical: 4,
    paddingHorizontal: 10,
    alignSelf: "flex-start",
  },
  badgeText: { fontSize: 11, fontWeight: "700" },
  sectionTitle: {
    ...type.h1,
    color: colors.textPrimary,
    marginBottom: spacing.md,
  },
});
