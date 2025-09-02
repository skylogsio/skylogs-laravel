import { useRouter } from "next/navigation";

import { Card, CardContent, Typography, Box, Chip, LinearProgress } from "@mui/material";
import { styled, keyframes } from "@mui/material/styles";
import { TbCircleCheck, TbAlertTriangle, TbExclamationCircle } from "react-icons/tb";

import type { IStatusCard, StateType } from "@/@types/status";
import { formatTimeAgo } from "@/utils/general";

const pulse = keyframes`
  0% { opacity: 1; }
  50% { opacity: 0.6; }
  100% { opacity: 1; }
`;

const glow = keyframes`
  0% { box-shadow: 0 0 5px rgba(255, 193, 7, 0.3); }
  50% { box-shadow: 0 0 20px rgba(255, 193, 7, 0.6); }
  100% { box-shadow: 0 0 5px rgba(255, 193, 7, 0.3); }
`;

const criticalGlow = keyframes`
  0% { box-shadow: 0 0 5px rgba(244, 67, 54, 0.3); }
  50% { box-shadow: 0 0 20px rgba(244, 67, 54, 0.6); }
  100% { box-shadow: 0 0 5px rgba(244, 67, 54, 0.3); }
`;

const shimmer = keyframes`
  0% { background-position: -200px 0; }
  100% { background-position: calc(200px + 100%) 0; }
`;

const StateCard = styled(Card)<{ state: StateType }>(({ theme, state }) => ({
  width: 340,
  background:
    state === "resolved"
      ? "linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%)"
      : state === "warning"
        ? "linear-gradient(135deg, #fff8e1 0%, #ffecb3 100%)"
        : "linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%)",
  border: `2px solid ${
    state === "resolved" ? "#4caf50" : state === "warning" ? "#ff9800" : "#f44336"
  }`,
  borderRadius: 16,
  position: "relative",
  overflow: "hidden",
  transition: "all 0.3s ease-in-out",
  cursor: "pointer",
  animation:
    state === "warning"
      ? `${glow} 2s infinite ease-in-out`
      : state === "critical"
        ? `${criticalGlow} 1.5s infinite ease-in-out`
        : "none",
  "&:hover": {
    transform: "translateY(-4px) scale(1.02)",
    boxShadow: theme.shadows[8]
  },
  "&::before": {
    content: "''",
    position: "absolute",
    top: 0,
    left: "-200px",
    width: "200px",
    height: "100%",
    background: "linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent)",
    animation: `${shimmer} 3s infinite`
  }
}));

const StateIcon = styled(Box)<{ state: StateType }>(({ state }) => ({
  display: "flex",
  alignItems: "center",
  justifyContent: "center",
  width: 40,
  height: 40,
  borderRadius: "50%",
  backgroundColor: state === "resolved" ? "#4caf50" : state === "warning" ? "#ff9800" : "#f44336",
  color: "white",
  animation: state !== "resolved" ? `${pulse} 2s infinite ease-in-out` : "none"
}));

const CounterChip = styled(Chip)<{ severity: "warning" | "critical" }>(({ severity }) => ({
  height: 24,
  fontSize: "0.75rem",
  fontWeight: 600,
  backgroundColor: severity === "critical" ? "#f44336" : "#ff9800",
  color: "white",
  "& .MuiChip-label": {
    padding: "0 8px"
  }
}));

const HealthBar = styled(LinearProgress)<{ state: StateType }>(({ state }) => ({
  height: 6,
  borderRadius: 3,
  backgroundColor: "rgba(255,255,255,0.3)",
  "& .MuiLinearProgress-bar": {
    backgroundColor: state === "resolved" ? "#4caf50" : state === "warning" ? "#ff9800" : "#f44336",
    borderRadius: 3
  }
}));

const getStatusIcon = (state: StateType) => {
  switch (state) {
    case "resolved":
      return <TbCircleCheck size={20} />;
    case "warning":
      return <TbAlertTriangle size={20} />;
    case "critical":
      return <TbExclamationCircle size={20} />;
    default:
      return <TbCircleCheck size={20} />;
  }
};

const getHealthPercentage = (state: StateType, criticalCount: number, warningCount: number) => {
  if (state === "resolved" && criticalCount === 0 && warningCount === 0) return 100;
  if (state === "resolved") return 85;
  if (state === "warning") return 60;
  return 25;
};

interface StatusMonitoringCardsProps {
  info: IStatusCard;
  alertRulePagePath?: string; // Optional prop to customize the path
}

const StatusMonitoringCards = ({
  info,
  alertRulePagePath = "/alert-rule" // Default path, can be customized
}: StatusMonitoringCardsProps) => {
  const router = useRouter();

  const handleCardClick = () => {
    // Create filters object based on the status card info
    const filters: Record<string, unknown> = {};

    // Add tags filter if the status card has tags
    if (info.tags && info.tags.length > 0) {
      filters.tags = info.tags;
    }

    // Add status filter based on the card state
    if (info.state === "critical") {
      filters.status = "critical";
    } else if (info.state === "warning") {
      filters.status = "warning";
    }

    // You can add more filters based on your requirements
    // For example, if you have endpoint information:
    // if (info.endpointId) {
    //   filters.endpointId = info.endpointId;
    // }

    // Create URL with filters
    const searchParams = new URLSearchParams();
    if (Object.keys(filters).length > 0) {
      searchParams.set("filters", encodeURIComponent(JSON.stringify(filters)));
    }

    const url = `${alertRulePagePath}${searchParams.toString() ? `?${searchParams.toString()}` : ""}`;

    // Navigate to alert rule page with filters
    router.push(url);
  };

  return (
    <StateCard key={info.id} state={info.state} onClick={handleCardClick}>
      <CardContent
        sx={{ padding: "16px !important", height: "100%", position: "relative", zIndex: 1 }}
      >
        <Box
          sx={{
            display: "flex",
            justifyContent: "space-between",
            alignItems: "flex-start",
            mb: 1
          }}
        >
          <Typography
            variant="h6"
            sx={{
              fontWeight: 700,
              fontSize: "1.1rem",
              color: "#333",
              lineHeight: 1.2,
              maxWidth: "160px"
            }}
          >
            {info.name}
          </Typography>
          <StateIcon state={info.state}>{getStatusIcon(info.state)}</StateIcon>
        </Box>
        <Box sx={{ display: "flex", gap: 1, mb: 2, flexWrap: "wrap" }}>
          {info.criticalCount > 0 && (
            <CounterChip
              label={`${info.criticalCount} Critical`}
              severity="critical"
              size="small"
            />
          )}
          {info.warningCount > 0 && (
            <CounterChip label={`${info.warningCount} Warning`} severity="warning" size="small" />
          )}
          {info.criticalCount === 0 && info.warningCount === 0 && (
            <Chip
              label="All Systems OK"
              size="small"
              sx={{
                backgroundColor: "#4caf50",
                color: "white",
                fontWeight: 600
              }}
            />
          )}
        </Box>

        {/* Display tags if available */}
        {info.tags && info.tags.length > 0 && (
          <Box sx={{ mb: 1.5 }}>
            <Typography
              variant="caption"
              sx={{ color: "#666", fontWeight: 500, mb: 0.5, display: "block" }}
            >
              Tags
            </Typography>
            <Box sx={{ display: "flex", gap: 0.5, flexWrap: "wrap" }}>
              {info.tags.slice(0, 3).map((tag, index) => (
                <Chip
                  key={index}
                  label={tag}
                  size="small"
                  variant="outlined"
                  sx={{
                    height: 20,
                    fontSize: "0.65rem",
                    borderColor: "#ccc",
                    color: "#666"
                  }}
                />
              ))}
              {info.tags.length > 3 && (
                <Chip
                  label={`+${info.tags.length - 3}`}
                  size="small"
                  variant="outlined"
                  sx={{
                    height: 20,
                    fontSize: "0.65rem",
                    borderColor: "#ccc",
                    color: "#666"
                  }}
                />
              )}
            </Box>
          </Box>
        )}

        <Box sx={{ mb: 1.5 }}>
          <Typography
            variant="caption"
            sx={{ color: "#666", fontWeight: 500, mb: 0.5, display: "block" }}
          >
            System Health
          </Typography>
          <HealthBar
            variant="determinate"
            value={getHealthPercentage(info.state, info.criticalCount, info.warningCount)}
            state={info.state}
          />
        </Box>
        <Box sx={{ display: "flex", justifyContent: "space-between", alignItems: "center" }}>
          <Typography variant="caption" sx={{ color: "#666", fontWeight: 500 }}>
            Updated {formatTimeAgo(info.updatedAt)}
          </Typography>
          <Box
            sx={{
              width: 8,
              height: 8,
              borderRadius: "50%",
              backgroundColor:
                info.state === "resolved"
                  ? "#4caf50"
                  : info.state === "warning"
                    ? "#ff9800"
                    : "#f44336",
              animation: info.state !== "resolved" ? `${pulse} 1s infinite ease-in-out` : "none"
            }}
          />
        </Box>
      </CardContent>
    </StateCard>
  );
};

export default StatusMonitoringCards;
