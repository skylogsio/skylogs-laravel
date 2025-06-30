"use client";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { useRef, useState } from "react";

import type { IAlertRule } from "@/@types/alertRule";
import type { CreateUpdateModal } from "@/@types/global";
import AlertRuleActionColumn from "@/app/[locale]/alert-rule/AlertRuleActionColumn";
import TagsCell from "@/app/[locale]/alert-rule/TagsCell";
import AlertRuleStatus from "@/components/AlertRule/AlertRuleStatus";
import AlertRuleType from "@/components/AlertRule/AlertRuleType";
import AlertRuleNotifyModal from "@/components/AlertRule/Notify/AlertRuleNotifyModal";
import Table from "@/components/Table/SmartTable";
import type { TableComponentRef } from "@/components/Table/types";

import AlertRuleFilter from "./AlertRuleFilter";
import AlertRuleModal from "./AlertRuleModal";
import DeleteAlertRuleModal from "./DeleteAlertRuleModal";

export default function AlertRule() {
  const tableRef = useRef<TableComponentRef>(null);
  const pathname = usePathname();
  const [modalData, setModalData] = useState<CreateUpdateModal<IAlertRule>>(null);
  const [deleteModalData, setDeleteModalData] = useState<IAlertRule | null>(null);

  function handleRefreshData() {
    if (tableRef.current) {
      tableRef.current.refreshData();
    }
  }

  function handleAfterDelete() {
    handleRefreshData();
    setDeleteModalData(null);
  }

  return (
    <>
      <Table<IAlertRule>
        ref={tableRef}
        title="Alert Rule"
        url="alert-rule"
        searchKey="alertname"
        filterComponent={({ onChange }) => <AlertRuleFilter onChange={onChange} />}
        defaultPageSize={10}
        columns={[
          { header: "Row", accessorFn: (_, index) => ++index },
          {
            header: "Name",
            cell: ({ row }) => (
              <Link href={`${pathname}/${row.original.id}`}>{row.original.name}</Link>
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
                rowId={row.original.id}
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
    </>
  );
}
