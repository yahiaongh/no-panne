import React from "react";
import { View, Text, StyleSheet } from "react-native";
import { Ionicons } from "@expo/vector-icons";
import { useApp } from "../context/AppContext";
import { colors, spacing } from "../theme/theme";
import { MISSION_STATUSES } from "../data/mockData";

export default function Timeline({ currentIndex }) {
  const { t, isRTL } = useApp();

  return (
    <View style={[styles.wrap, isRTL && { flexDirection: "row-reverse" }]}>
      {MISSION_STATUSES.map((statusKey, i) => {
        const done = i <= currentIndex;
        const isLast = i === MISSION_STATUSES.length - 1;
        return (
          <View key={statusKey} style={styles.stepWrap}>
            <View style={styles.nodeRow}>
              <View style={[styles.node, done && styles.nodeDone]}>
                {done ? (
                  <Ionicons name="checkmark" size={12} color="#1C1300" />
                ) : (
                  <View style={styles.nodeDot} />
                )}
              </View>
              {!isLast && (
                <View
                  style={[
                    styles.connector,
                    i < currentIndex && { backgroundColor: colors.amber },
                    isRTL && { transform: [{ scaleX: -1 }] },
                  ]}
                />
              )}
            </View>
            <Text
              style={[styles.label, done && { color: colors.textPrimary }]}
              numberOfLines={1}
            >
              {t(statusKey)}
            </Text>
          </View>
        );
      })}
    </View>
  );
}

const styles = StyleSheet.create({
  wrap: { flexDirection: "row", alignItems: "flex-start" },
  stepWrap: { flex: 1, alignItems: "center" },
  nodeRow: { flexDirection: "row", alignItems: "center", width: "100%" },
  node: {
    width: 22,
    height: 22,
    borderRadius: 11,
    backgroundColor: colors.bgCardAlt,
    borderWidth: 1,
    borderColor: colors.border,
    alignItems: "center",
    justifyContent: "center",
    marginHorizontal: "auto",
  },
  nodeDone: { backgroundColor: colors.amber, borderColor: colors.amber },
  nodeDot: { width: 5, height: 5, borderRadius: 3, backgroundColor: colors.textFaint },
  connector: { flex: 1, height: 2, backgroundColor: colors.border },
  label: {
    color: colors.textFaint,
    fontSize: 10,
    fontWeight: "600",
    marginTop: 6,
    textAlign: "center",
  },
});
