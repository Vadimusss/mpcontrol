import { useState } from 'react';
import { usePage, Link } from '@inertiajs/react';

export default function Customers({ shopId, customers }) {
    const { auth } = usePage().props;
    const [addCustomerModalIsOpen, setAddCustomerModalIsOpen] = useState(false);

    const handleAddCustomerButtonClick = (e) => {
        setAddCustomerModalIsOpen(true);
    };

    const closeAddCustomerModal = (() => {
        setAddCustomerModalIsOpen(false);
    });

    return (
        <>
            <p>Пользователи:</p>
            < div className='flex border border-gray-300 rounded-md shadow-sm p-2 w-fit max-w-xl gap-2 flex-wrap bg-gray-100'>
                {customers.map((customer) =>
                    <div key={customer.id} className='flex items-center gap-x-2 border border-gray-300 rounded-md px-2 py-1 bg-white'>
                        {customer.email}
                        <Link
                            as="button"
                            preserveScroll
                            method="patch"
                            href={route('shops.update', shopId)}
                            data = { { customerId: customer.id } }
                            className='border border-gray-300 rounded-md px-2 cursor-pointer hover:bg-gray-100'>
                            X
                        </Link>
                    </div>
                )}
            </div>
        </>
    );
}
