<script setup>
import Table from '@/components/ui/Table.vue';
import TableHeader from '@/components/ui/TableHeader.vue';
import TableBody from '@/components/ui/TableBody.vue';
import TableRow from '@/components/ui/TableRow.vue';
import TableHead from '@/components/ui/TableHead.vue';
import TableCell from '@/components/ui/TableCell.vue';

defineProps({
  columns: Array,
  rows: Array,
  loading: { type: Boolean, default: false },
  emptyText: { type: String, default: 'No records found.' },
});
</script>

<template>
  <div class="rounded-lg border border-border bg-card overflow-hidden">
    <Table>
      <TableHeader>
        <TableRow class="hover:bg-transparent">
          <TableHead v-for="col in columns" :key="col.key">
            {{ col.label }}
          </TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow v-if="loading">
          <TableCell :colspan="columns.length" class="text-center py-10 text-muted-foreground">
            Loading...
          </TableCell>
        </TableRow>
        <TableRow v-else-if="!rows?.length">
          <TableCell :colspan="columns.length" class="text-center py-10 text-muted-foreground">
            {{ emptyText }}
          </TableCell>
        </TableRow>
        <TableRow v-else v-for="(row, i) in rows" :key="i">
          <TableCell v-for="col in columns" :key="col.key">
            <slot :name="col.key" :row="row" :value="row[col.key]">
              {{ row[col.key] ?? '-' }}
            </slot>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>
</template>
