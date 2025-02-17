"use client";
import { useRef, useState } from "react";

import { alpha, Chip } from "@mui/material";
import type { Cell } from "@tanstack/table-core";
import { BsTelegram, BsChatDotsFill, BsTelephoneFill, BsMicrosoftTeams } from "react-icons/bs";

import type { IEndpoint } from "@/@types/endpoint";
import { CreateUpdateModal } from "@/@types/global";
import ActionColumn from "@/components/ActionColumn";
import Table from "@/components/Table";
import { type TableComponentRef } from "@/components/Table/types";

import EndPointModal from "./EndPointModal";

const ENDPOINT_TYPE_CHIP = {
  sms: {
    title: "SMS",
    color: "#13C82B",
    icon: <BsChatDotsFill style={{ padding: "0.2rem" }} color="#13C82B" />
  },
  telegram: {
    title: "Telegram",
    color: "#4880FF",
    icon: <BsTelegram style={{ padding: "0.2rem" }} color="#4880FF" />
  },
  teams: {
    title: "Teams",
    color: "#454DB3",
    icon: <BsMicrosoftTeams style={{ padding: "0.2rem" }} color="#454DB3" />
  },
  call: {
    title: "Call",
    color: "#B65DFE",
    icon: <BsTelephoneFill style={{ padding: "0.2rem" }} color="#B65DFE" />
  }
};

export default function EndPoints() {
  const tableRef = useRef<TableComponentRef>(null);
  const [modalData, setModalData] = useState<CreateUpdateModal<IEndpoint>>(null);

  function handleClose() {
    setModalData(null);
  }

  function handleOpen() {
    setModalData("NEW");
  }

  function handleEdit(data: IEndpoint) {
    setModalData(data);
  }

  function handleRefreshData() {
    if (tableRef.current) {
      tableRef.current.refreshData();
    }
  }

  function renderChipType(cell: Cell<IEndpoint, unknown>) {
    const Avatar = ENDPOINT_TYPE_CHIP[cell.getValue() as keyof typeof ENDPOINT_TYPE_CHIP].icon;
    const color = ENDPOINT_TYPE_CHIP[cell.getValue() as keyof typeof ENDPOINT_TYPE_CHIP].color;
    return (
      <Chip
        avatar={Avatar}
        sx={{
          backgroundColor: alpha(color, 0.1),
          color,
          borderRadius: "0.4rem"
        }}
        label={ENDPOINT_TYPE_CHIP[cell.getValue() as keyof typeof ENDPOINT_TYPE_CHIP].title}
      />
    );
  }

  return (
    <>
      <Table<IEndpoint>
        ref={tableRef}
        title="EndPoints"
        url="endpoint"
        defaultPage={1}
        defaultPageSize={10}
        columns={[
          { header: "Row", accessorFn: (_, index) => index },
          { header: "Name", accessorKey: "name" },
          {
            header: "Type",
            accessorKey: "type",
            cell: ({ cell }) => renderChipType(cell)
          },
          {
            header: "Value",
            accessorFn: (row) => (row.type === "telegram" ? row.chatId : row.value)
          },
          {
            header: "Action",
            cell: ({ row }) => <ActionColumn onEdit={() => handleEdit(row.original)} />
          }
        ]}
        onCreate={handleOpen}
      />
      {modalData && (
        <EndPointModal
          open={!!modalData}
          onClose={handleClose}
          data={modalData}
          onSubmit={handleRefreshData}
        />
      )}
    </>
  );
}
