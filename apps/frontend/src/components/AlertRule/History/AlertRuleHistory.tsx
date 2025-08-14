import type { IAlertRule } from "@/@types/alertRule";
import ApiAndNotificationAlertHistory from "@/components/AlertRule/History/ApiAndNotificationAlertHistory";
import GeneralAlertHistory from "@/components/AlertRule/History/GeneralAlertHistory";
import type { AlertRuleType } from "@/utils/alertRuleUtils";

export default function AlertRuleHistory({
  alertId,
  type
}: {
  alertId: IAlertRule["id"];
  type: AlertRuleType;
}) {
  switch (type) {
    case "api":
    case "notification":
      return <ApiAndNotificationAlertHistory alertId={alertId} />;
    case "prometheus":
    case "grafana":
      return <GeneralAlertHistory alertId={alertId} />;
    default:
      return null;
  }
}
