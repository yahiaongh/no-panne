import React from "react";
import { Text, TouchableOpacity, StyleSheet, View } from "react-native";
import { Ionicons } from "@expo/vector-icons";
import { colors, radii, spacing, type } from "../theme/theme";

export default function Button({
  label,
  onPress,
  variant = "primary", // primary | ghost | danger | dark
  icon,
  iconPosition = "start",
  fullWidth = true,
  size = "md", // md | sm
  disabled = false,
}) {
  const styles = getStyles(variant, size, disabled);

  return (
    <TouchableOpacity
      activeOpacity={0.85}
      onPress={disabled ? undefined : onPress}
      style={[styles.base, fullWidth && { width: "100%" }]}
    >
      <View style={styles.row}>
        {icon && iconPosition === "start" && (
          <Ionicons
            name={icon}
            size={size === "sm" ? 15 : 18}
            color={styles.textColor.color}
            style={{ marginEnd: 8 }}
          />
        )}
        <Text style={styles.text}>{label}</Text>
        {icon && iconPosition === "end" && (
          <Ionicons
            name={icon}
            size={size === "sm" ? 15 : 18}
            color={styles.textColor.color}
            style={{ marginStart: 8 }}
          />
        )}
      </View>
    </TouchableOpacity>
  );
}

function getStyles(variant, size, disabled) {
  const bg = {
    primary: colors.amber,
    danger: colors.danger,
    dark: colors.bgCard,
    ghost: "transparent",
  }[variant];

  const textColor = {
    primary: "#1C1300",
    danger: "#FFF5F5",
    dark: colors.textPrimary,
    ghost: colors.textSecondary,
  }[variant];

  return StyleSheet.create({
    base: {
      backgroundColor: bg,
      borderRadius: radii.pill,
      paddingVertical: size === "sm" ? 10 : 15,
      paddingHorizontal: spacing.xl,
      borderWidth: variant === "ghost" ? 1 : 0,
      borderColor: colors.border,
      opacity: disabled ? 0.5 : 1,
      alignItems: "center",
      justifyContent: "center",
    },
    row: { flexDirection: "row", alignItems: "center", justifyContent: "center" },
    text: {
      color: textColor,
      fontSize: size === "sm" ? 13 : 15,
      fontWeight: "700",
    },
    textColor: { color: textColor },
  });
}
