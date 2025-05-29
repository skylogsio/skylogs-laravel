import type { AxiosResponse } from "axios";

import type { ModalContainerProps } from "@/components/Modal/types";

export type CreateUpdateModal<T> = T | "NEW" | null;

export interface BasicCreateOrUpdateModalProps
  extends Pick<ModalContainerProps, "open" | "onClose"> {
  onSubmit: () => void;
}

export type ConnectionStatusType = "connected" | "disconnected" | "warning";

export type ServerSelectableDataType = Array<{ id: string; name: string }>;

//TODO: Should add the type of message which comes from the server
type ErrorResponse = { status: false; message: unknown };
type SuccessResponse<T> = { status: true; data: T };
export type ServerResponse<T> = AxiosResponse<ErrorResponse | SuccessResponse<T>>;

export interface IServerResponseTabularData<T> {
  data: T[];
  current_page: number;
  last_page: number;
  from: number;
  first_page_url: string;
  last_page_url: string;
  next_page_url: string;
  path: string;
  per_page: number;
  prev_page_url: string;
  to: number;
  total: number;
  links: Array<{
    url: string;
    label: string;
    active: boolean;
  }>;
}

export interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}
