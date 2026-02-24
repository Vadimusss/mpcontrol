import { useState } from 'react';
import { Link, router } from '@inertiajs/react';

export default function Customers({ shopId, customers, canManageUsers}) {
    const [updatingRoles, setUpdatingRoles] = useState({});

    const handleRoleToggle = (customerId, currentRole) => {
        if (!canManageUsers) return;
        
        setUpdatingRoles(prev => ({ ...prev, [customerId]: true }));
        
        const newRole = currentRole === 'admin' ? 'manager' : 'admin';
        
        router.patch(route('shops.update', shopId), {
            customerId,
            role: newRole,
            type: 'updateCustomerRole'
        }, {
            preserveScroll: true,
            onFinish: () => {
                setUpdatingRoles(prev => ({ ...prev, [customerId]: false }));
            }
        });
    };

    return (
        <>
            <p className='font-semibold mt-2'>Пользователи:</p>
            <div className='flex border border-gray-300 rounded-md shadow-sm p-2 w-fit max-w-xl gap-2 flex-wrap bg-gray-100'>
                {customers.map((customer) => {
                    const role = customer.pivot?.role || 'manager';
                    const isAdmin = role === 'admin';
                    const isUpdating = updatingRoles[customer.id] || false;
                    
                    return (
                        <div key={customer.id} className='flex items-center gap-x-2 border border-gray-300 rounded-md px-2 py-1 bg-white'>
                            {customer.email}
                            
                            {canManageUsers && (
                                <button
                                    type="button"
                                    onClick={() => handleRoleToggle(customer.id, role)}
                                    disabled={isUpdating}
                                    className={`border border-gray-300 rounded-md min-w-[26px] cursor-pointer hover:bg-gray-100 ${
                                        isAdmin ? 'text-yellow-500' : 'text-gray-400'
                                    } ${isUpdating ? 'opacity-50 cursor-not-allowed' : ''}`}
                                    title={isAdmin ? 'Администратор (клик чтобы сделать менеджером)' : 'Менеджер (клик чтобы сделать администратором)'}
                                >
                                    {isUpdating ? '...' : '★'}
                                </button>
                            )}
                            
                            {canManageUsers && (
                                <Link
                                    as="button"
                                    preserveScroll
                                    method="patch"
                                    href={route('shops.update', shopId)}
                                    data={{ customerId: customer.id, type: 'deleteCustomer' }}
                                    className='border border-gray-300 rounded-md min-w-[26px] cursor-pointer hover:bg-gray-100'
                                >
                                    X
                                </Link>
                            )}
                        </div>
                    );
                })}
            </div>
        </>
    );
}
