import { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import ReportCard from '@/Pages/Reports/Components/ReportCard';
import { Head } from '@inertiajs/react';

export default function WorkSpaces({ shop, reports, goodLists }) {
    console.log(shop);
    console.log(reports);
    console.log(goodLists);

    return (
        <AuthenticatedLayout
            navigation={true}
            shopId={shop.id}
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    {shop.name}
                </h2>
            }>
            <Head title={`Магазин ${shop.name}`} />
            <div className="max-w-2xl mx-auto">
                <div className="p-2 sm:p-3 lg:p-6">
                    <h2 className="text-xl font-bold mb-3">Выгрузки</h2>
                    {reports.map((report) =>
                        <ReportCard 
                            goodLists={goodLists}
                            report={report}
                            key={report.id}
                        />)}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
