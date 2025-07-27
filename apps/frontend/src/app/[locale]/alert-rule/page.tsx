"use client";
import { usePathname, useRouter } from "next/navigation";
import { useRef, useState } from "react";

import { Stack, Typography } from "@mui/material";
import { FaThumbtack } from "react-icons/fa6";

import type { IAlertRule } from "@/@types/alertRule";
import type { CreateUpdateModal } from "@/@types/global";
import AlertRuleActionColumn from "@/app/[locale]/alert-rule/AlertRuleActionColumn";
import TagsCell from "@/app/[locale]/alert-rule/TagsCell";
import AlertRuleStatus from "@/components/AlertRule/AlertRuleStatus";
import AlertRuleType from "@/components/AlertRule/AlertRuleType";
import GroupActionModal from "@/components/AlertRule/GroupActionModal";
import AlertRuleNotifyModal from "@/components/AlertRule/Notify/AlertRuleNotifyModal";
import Table from "@/components/Table/SmartTable";
import type { TableComponentRef } from "@/components/Table/types";

import AlertRuleFilter from "./AlertRuleFilter";
import AlertRuleModal from "./AlertRuleModal";
import DeleteAlertRuleModal from "./DeleteAlertRuleModal";

export default function AlertRule() {
  const router = useRouter();
  const tableRef = useRef<TableComponentRef>(null);
  const pathname = usePathname();
  const [modalData, setModalData] = useState<CreateUpdateModal<IAlertRule>>(null);
  const [deleteModalData, setDeleteModalData] = useState<IAlertRule | null>(null);
  const [openGroupActionModal, setOpenGroupActionModal] = useState<boolean>(false);

  function handleRefreshData() {
    if (tableRef.current) {
      tableRef.current.refreshData();
    }
  }

  function handleAfterDelete() {
    handleRefreshData();
    setDeleteModalData(null);
  }

  function handleAfterGroupAction() {
    handleRefreshData();
    setOpenGroupActionModal(false);
  }

  return (
    <>
      <Table<IAlertRule>
        ref={tableRef}
        title="Alert Rule"
        url="alert-rule"
        onGroupActionClick={() => setOpenGroupActionModal(true)}
        searchKey="alertname"
        filterComponent={({ onChange }) => <AlertRuleFilter onChange={onChange} />}
        defaultPageSize={10}
        columns={[
          {
            header: "Row",
            accessorFn: (_, index) => ++index
          },
          {
            header: "Name",
            cell: ({ row }) => (
              <Stack
                direction="row"
                alignItems="center"
                justifyContent="center"
                spacing={1}
                onClick={() => router.push(`${pathname}/${row.original.id}`)}
                sx={{ color: ({ palette }) => palette.grey[400], cursor: "pointer" }}
              >
                {row.original.isPinned && (
                  <FaThumbtack size="0.9rem" style={{ transform: "rotate(-45deg)" }} />
                )}
                <Typography variant="body2" color="textPrimary">
                  {row.original.name}
                </Typography>
              </Stack>
            )
          },
          {
            header: "Type",
            cell: ({ row }) => <AlertRuleType type={row.original.type} />
          },
          {
            header: "Notify",
            cell: ({ row }) => (
              <AlertRuleNotifyModal
                alertId={row.original.id}
                numberOfEndpoints={row.original.count_endpoints}
                onClose={handleRefreshData}
              />
            )
          },
          {
            header: "Status",
            cell: ({ row }) => (
              <AlertRuleStatus
                id={row.original.id}
                status={row.original.status_label}
                onAfterResolve={handleRefreshData}
              />
            )
          },
          {
            header: "Tags",
            cell: ({ row }) => <TagsCell tags={row.original.tags} />
          },
          {
            header: "Action",
            cell: ({ row }) => (
              <AlertRuleActionColumn
                isSilent={row.original.is_silent}
                isPinned={row.original.isPinned}
                rowId={row.original.id}
                refreshData={handleRefreshData}
                onEdit={() => setModalData(row.original)}
                onDelete={() => setDeleteModalData(row.original)}
              />
            )
          }
        ]}
        onCreate={() => setModalData("NEW")}
      />
      {modalData && (
        <AlertRuleModal
          open={!!modalData}
          onClose={() => setModalData(null)}
          data={modalData}
          onSubmit={handleRefreshData}
        />
      )}
      {deleteModalData && (
        <DeleteAlertRuleModal
          open={!!deleteModalData}
          onClose={() => setDeleteModalData(null)}
          onAfterDelete={handleAfterDelete}
          data={deleteModalData}
        />
      )}
      {openGroupActionModal && (
        <GroupActionModal open={openGroupActionModal} onClose={handleAfterGroupAction} />
      )}
    </>
  );
}
