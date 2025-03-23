"use client";
import { useRef, useState } from "react";

import type { IAlertRule } from "@/@types/alertRule";
import type { CreateUpdateModal } from "@/@types/global";
import AlertRuleFilter from "@/app/[locale]/alert-rule/AlertRuleFilter";
import DeleteAlertRuleModal from "@/app/[locale]/alert-rule/DeleteAlertRuleModal";
import ActionColumn from "@/components/ActionColumn";
import AlertRuleStatus from "@/components/AlertRule/AlertRuleStatus";
import AlertRuleType from "@/components/AlertRule/AlertRuleType";
import Table from "@/components/Table";
import type { TableComponentRef } from "@/components/Table/types";

import AlertRuleModal from "./AlertRuleModal";

export default function AlertRule() {
  const tableRef = useRef<TableComponentRef>(null);
  const [modalData, setModalData] = useState<CreateUpdateModal<IAlertRule>>(null);
  const [deleteModalData, setDeleteModalData] = useState<IAlertRule | null>(null);

  function handleRefreshData() {
    if (tableRef.current) {
      tableRef.current.refreshData();
    }
  }

  return (
    <>
      <Table<IAlertRule>
        ref={tableRef}
        title="Alert Rule"
        url="alert-rule"
        filterComponent={({ onChange }) => <AlertRuleFilter onChange={onChange} />}
        defaultPageSize={10}
        columns={[
          { header: "Row", accessorFn: (_, index) => ++index },
          { header: "Name", accessorKey: "name" },
          {
            header: "Type",
            cell: ({ row }) => <AlertRuleType type={row.original.type} />
          },
          {
            header: "Status",
            cell: ({ row }) => (
              <AlertRuleStatus onResolve={() => {}} status={row.original.status_label} />
            )
          },
          {
            header: "Action",
            cell: ({ row }) => <ActionColumn onDelete={() => setDeleteModalData(row.original)} />
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
          onDelete={handleRefreshData}
          data={deleteModalData}
        />
      )}
    </>
  );
}
