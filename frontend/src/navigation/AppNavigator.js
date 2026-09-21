import React, { useState } from "react";
import { View, StyleSheet, SafeAreaView, StatusBar } from "react-native";
import { useApp } from "../context/AppContext";
import { colors } from "../theme/theme";
import TopBar from "../components/TopBar";
import HomeScreen from "../screens/HomeScreen";
import EmergencyRequestScreen from "../screens/EmergencyRequestScreen";
import TrackingScreen from "../screens/TrackingScreen";
import ReviewModal from "../screens/ReviewModal";

export default function AppNavigator() {
  const { isRTL } = useApp();
  const [view, setView] = useState("home"); // home | emergency | tracking
  const [reviewVisible, setReviewVisible] = useState(false);

  return (
    <SafeAreaView style={styles.safe}>
      <StatusBar barStyle="light-content" backgroundColor={colors.bgDeep} />
      <View style={[styles.root, isRTL && { direction: "rtl" }]}>
        <TopBar view={view} onChangeView={setView} />

        {view === "home" && (
          <HomeScreen onStartEmergency={() => setView("emergency")} />
        )}

        {view === "emergency" && (
          <EmergencyRequestScreen
            onCancel={() => setView("home")}
            onLaunchSearch={() => setView("tracking")}
          />
        )}

        {view === "tracking" && (
          <TrackingScreen onOpenReview={() => setReviewVisible(true)} />
        )}

        <ReviewModal
          visible={reviewVisible}
          onClose={() => setReviewVisible(false)}
          onSubmit={() => {
            setReviewVisible(false);
            setView("home");
          }}
        />
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: colors.bgDeep },
  root: { flex: 1, backgroundColor: colors.bgDeep },
});
