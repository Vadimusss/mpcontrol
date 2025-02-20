import React, { useState } from 'react';
import PrimaryButton from '@/Components/PrimaryButton';

export default function SalesFunnelReportCard({ shopId, report, goodLists, handleDownload }) {
    const [selectedGoodListId, setSelectedGoodListId] = useState(
        goodLists.length === 0 ? null : goodLists[0].id
    );

    const currentDate = new Date().toISOString().split('T')[0];
    const [selectedBeginDate, setSelectedBeginDate] = useState(currentDate);
    const [selectedEndDate, setSelectedEndDate] = useState(currentDate);

    return (
        <div className="border border-gray-300 rounded-md shadow-sm bg-white mb-2 pt-2 pl-2 pb-2 pr-10">
            <p className="mb-4"><span className='font-semibold text-gray-900'>Выгрузка:</span> {report.type.name}</p>
            <div className="mb-4 flex gap-x-8">
                <div className="mb-4 w-64">
                    <p className="block mb-2 text-sm font-medium text-gray-900">{selectedGoodListId === null ? 'Создайте список товаров' : 'Выберите список товаров'}</p>
                    <select
                        onChange={e => setSelectedGoodListId(e.target.value)}
                        className="mb-12 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2">
                        {goodLists && goodLists.map(({ id, name }) => <option value={id} key={id}>{name}</option>)}
                    </select>
                    <PrimaryButton
                        className="max-w-fit"
                        onClick={() => handleDownload(shopId, report, selectedBeginDate, selectedEndDate, selectedGoodListId)}
                        disabled={!selectedGoodListId}>
                        Скачать
                    </PrimaryButton>
                </div>
                <div className="w-40">
                    <label className="block mb-2 text-sm font-medium text-gray-900">Начальная дата:</label>
                    <input type="date" value={selectedBeginDate} onChange={(e) => setSelectedBeginDate(e.target.value)}
                        className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2 mb-4"
                    />
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
