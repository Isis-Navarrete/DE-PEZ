import Stack from '@elementor/ui/Stack';
import Typography from '@elementor/ui/Typography';
import { __ } from '@wordpress/i18n';
import Button from '@elementor/ui/Button';
import Box from '@elementor/ui/Box';
import Paper from '@elementor/ui/Paper';
import { useState, useRef, useEffect } from 'react';

export const ConversionBanner = () => {
	const [ visible, setVisible ] = useState( true );
	const [ isLoading, setIsLoading ] = useState( false );
	const [ imageWidth, setImageWidth ] = useState( 578 );
	const parentRef = useRef( null );

	useEffect( () => {
		const handleResize = () => {
			if ( parentRef.current ) {
				const parentWidth = parentRef.current.offsetWidth;
				setImageWidth( parentWidth < 800 ? 400 : 578 );
			}
		};

		handleResize();
		window.addEventListener( 'resize', handleResize );

		return () => {
			window.removeEventListener( 'resize', handleResize );
		};
	}, [] );

	if ( ! visible ) {
		return null;
	}

	return (
		<Box sx={ { pt: 2, pr: 2, pb: 1 } }>
			<Paper sx={ { width: '100%', px: 4, py: 3, position: 'relative' } }>
				<Box component="button" className="notice-dismiss" onClick={ async () => {
					try {
						await wp.ajax.post( 'ehp_dismiss_theme_notice', { nonce: window.ehp_cb.nonce } );
						setVisible( false );
					} catch ( e ) {
					}
				} }>
					<Box component="span" className="screen-reader-text">{ __( 'Dismiss this notice.', 'hello-biz' ) }</Box>
				</Box>
				<Stack ref={ parentRef } direction={ { xs: 'column', md: 'row' } } alignItems="center" justifyContent="space-between" sx={ { width: '100%', gap: 9 } }>
					<Stack direction="column" sx={ { flex: 1 } }>
						<Typography variant="h6" sx={ { color: 'text.primary' } }>
							{ __( 'Welcome to Hello Biz!', 'hello-biz' ) }
						</Typography>
						<Typography variant="body2" sx={ { color: 'text.secondary', mb: 2 } }>
							{
								__(
									'Here you’ll find links to some site settings that will help you set up and get running as soon as possible. With Hello Biz you’ll find creating your website is a breeze.',
									'hello-biz',
								)
							}
						</Typography>
						<Button disabled={ isLoading } variant="contained" sx={ { width: 'fit-content', mb: 2 } } onClick={ async () => {
							const { ehp_cb: { isHelloPlusInstalled, buttonUrl, nonceInstall } } = window;
							if ( isHelloPlusInstalled ) {
								window.location.href = buttonUrl;
							} else {
								try {
									const data = {
										_wpnonce: nonceInstall,
										slug: 'hello-plus', // ToDo ensure this is the right slug, for now it is free.
									};

									setIsLoading( true );

									const response = await wp.ajax.post( 'hello_biz_install_hp', data );

									if ( response.activateUrl ) {
										window.location.href = response.activateUrl;
									} else {
										throw new Error();
									}
								} catch ( error ) {
									// eslint-disable-next-line no-alert
									alert( __( 'Currently the plugin isn’t available. Please try again later. You can also contact our support at: wordpress.org/plugins/hello-plus', 'hello-biz' ) );
								} finally {
									setIsLoading( false );
								}
							}
						} }>
							{ isLoading ? __( 'Installing Hello Plus', 'hello-biz' ) : window.ehp_cb.buttonText }
						</Button>
						{ window.ehp_cb.showText && ( <Typography variant="body2" sx={ { color: 'text.tertiary' } }>
							{
								__(
									'By clicking "Begin setup" I agree to install and activate the Hello+ plugin.',
									'hello-biz',
								)
							}
						</Typography> ) }
					</Stack>
					<Box
						component="img"
						src={ window.ehp_cb.imageUrl }
						sx={ {
							width: { sm: 350, md: 450, lg: imageWidth },
							aspectRatio: '289/98',
							flex: 1,
						} }
					/>
				</Stack>
			</Paper>
		</Box>
	);
};
