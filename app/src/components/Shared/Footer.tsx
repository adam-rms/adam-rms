import React from "react";

function Footer(){
    return (
        <footer className="footer">
            <div className="container mx-auto px-6">
                <div className="mt-16 border-t-2 border-gray-300 flex flex-col items-center">
                    <div className="sm:w-2/3 text-center py-6">
                        <p className="text-sm text-gray-600 font-bold mb-2 dark:text-white">
                            © 2021 <a href="https://bithell.studio/">Bithell Studios Ltd.</a> All Rights Reserved.
                        </p>
                    </div>
                </div>
            </div>
        </footer>
    );
}

export default Footer;