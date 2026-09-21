import React from "react";
import { View, Text, TouchableOpacity, StyleSheet } from "react-native";
import { Ionicons } from "@expo/vector-icons";
import { useApp } from "../context/AppContext";
import { colors, spacing } from "../theme/theme";

export default function BottomNav() {
  const { t, isRTL } = useApp();
  const [active, setActive] = React.useState("navHome");

  const items = [
    { id: "navHome", icon: "home" },
    { id: "navRequests", icon: "receipt-outline" },
    { id: "navFavorites", icon: "heart-outline" },
    { id: "navAccount", icon: "person-outline" },
  ];

  return (
    <View style={[styles.wrap, isRTL && { flexDirection: "row-reverse" }]}>
      {items.map((it) => {
        const isActive = active === it.id;
        return (
          <TouchableOpacity
            key={it.id}
            style={styles.item}
            onPress={() => setActive(it.id)}
          >
            <Ionicons
              name={isActive ? it.icon.replace("-outline", "") : it.icon}
              size={21}
              color={isActive ? colors.amber : colors.textFaint}
            />
            <Text
              style={[styles.label, isActive && { color: colors.amber }]}
              numberOfLines={1}
            >
              {t(it.id)}
            </Text>
          </TouchableOpacity>
        );
      })}
    </View>
  );
}

const styles = StyleSheet.create({
  wrap: {
    flexDirection: "row",
    backgroundColor: colors.bgSurface,
    borderTopWidth: 1,
    borderTopColor: colors.borderMuted,
    paddingTop: spacing.sm,
    paddingBottom: spacing.md,
  },
  item: { flex: 1, alignItems: "center", gap: 3 },
  label: { fontSize: 10, fontWeight: "600", color: colors.textFaint, marginTop: 2 },
});
