import type { ModalContainerProps } from "@/components/Modal/types";

export interface DeleteModalProps
  extends Pick<ModalContainerProps, "onClose" | "open" | "children"> {
  onDelete?: () => void;
  isLoading?: boolean;
}
