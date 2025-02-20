import React, { useState } from 'react';
import PrimaryButton from '@/Components/PrimaryButton';

export default function StocksAndOrdersReportCard({ shopId, report, handleDownload }) {
    const currentDate = new Date().toISOString().split('T')[0];
    const [selectedBeginDate, setSelectedBeginDate] = useState(currentDate);
    const [selectedEndDate, setSelectedEndDate] = useState(currentDate);

    return (
        <div className="border border-gray-300 rounded-md shadow-sm bg-white mb-2 pt-2 pl-2 pb-2 pr-10">
            <p className="mb-4"><span className='font-semibold text-gray-900'>Выгрузка:</span> {report.type.name}</p>
            <div className="mb-4 flex gap-x-8 items-end">
                    <PrimaryButton
                        className="max-w-fit mr-auto"
                        onClick={() => handleDownload(shopId, report, selectedBeginDate, selectedEndDate)}>
                        Скачать
                    </PrimaryButton>
                <div className="w-40">
                    <label className="block mb-2 text-sm font-medium text-gray-900">Начальная дата:</label>
                    <input type="date" value={selectedBeginDate} onChange={(e) => setSelectedBeginDate(e.target.value)}
                        className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2"
                    />
                </div>
                <div className="w-40">
                    <label className="block mb-2 text-sm font-medium text-gray-900">Конечная дата:</label>
                    <input type="date" value={selectedEndDate} min={selectedBeginDate} max={currentDate} onChange={(e) =>
                        setSelectedEndDate(e.target.value)}
                        className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2"
                    />
                </div>
            </div>
        </div >
    );
}
