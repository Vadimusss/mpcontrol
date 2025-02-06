import { useState } from 'react';

export default function GoodsTable({ goods, selectedGoodsId, setSelectedGoodsId }) {
    const [checkState, setCheckState] = useState(
        goods.reduce((acc, good) => {
            acc[good.id] = false;
            return acc;
        }, {})
    );

    // console.log(selectedGoodsId);

    const handleOnChange = (id) => {
        if (!checkState[id]) {
            setSelectedGoodsId([...selectedGoodsId, id]);
        } else {
            setSelectedGoodsId(selectedGoodsId.filter((goodId) => goodId !== id));
        }

        setCheckState({...checkState, [id]: !checkState[id]  });
    };

    return (
        <table className="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
            <thead className="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" className="px-6 py-3">
                        nm_id
                    </th>
                    <th scope="col" className="px-6 py-3">
                        vendor_code
                    </th>
                    <th scope="col" className="px-4 py-3 text-center">
                        выбрать
                    </th>
                </tr>
            </thead>
            <tbody>
                {goods && goods.map((good) =>
                    <tr key={good.id} className="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <th scope="row" className="px-6 py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            {good.nm_id}
                        </th>
                        <td className="px-6 py-2">
                            {good.vendor_code}
                        </td>
                        <td className="px-4 py-2 text-center">
                            <input
                                type="checkbox"
                                className="rounded text-gray-900"
                                checked={checkState[good.id]}
                                onChange={() => handleOnChange(good.id)}
                            />
                        </td>
                    </tr>
                )}
            </tbody>
        </table>
    );
}